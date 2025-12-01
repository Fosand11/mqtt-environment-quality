<?php

namespace App\Services;

use App\Events\AlertTriggered;
use App\Events\SensorDataReceived;
use Illuminate\Support\Facades\Log;
use PhpMqtt\Client\ConnectionSettings;
use PhpMqtt\Client\Exceptions\MqttClientException;
use PhpMqtt\Client\MqttClient;

class MqttService
{
    private MqttClient $client;
    private ConnectionSettings $connectionSettings;

    public function __construct(
        private readonly SensorService $sensorService
    ) {
        $this->setupConnection();
    }

    /**
     * Configurar la conexión MQTT
     */
    private function setupConnection(): void
    {
        $host = config('mqtt.broker.host', env('MQTT_BROKER_HOST', 'localhost'));
        $port = config('mqtt.broker.port', env('MQTT_BROKER_PORT', 1883));
        $clientId = config('mqtt.client.id', env('MQTT_CLIENT_ID', 'laravel_mqtt_client'));

        $this->client = new MqttClient($host, $port, $clientId);

        $this->connectionSettings = (new ConnectionSettings)
            ->setKeepAliveInterval(30)  // Reducido de 60 a 30 segundos
            ->setConnectTimeout(10)
            ->setSocketTimeout(10)
            ->setResendTimeout(10)
            ->setLastWillTopic('clients/laravel')
            ->setLastWillMessage('Laravel client desconectado')
            ->setLastWillQualityOfService(1);

        // Solo configurar credenciales si están definidas
        $username = config('mqtt.broker.username', env('MQTT_BROKER_USERNAME'));
        $password = config('mqtt.broker.password', env('MQTT_BROKER_PASSWORD'));

        if (!empty($username) && !empty($password)) {
            $this->connectionSettings
                ->setUsername($username)
                ->setPassword($password);
        }
    }

    /**
     * Conectar al broker MQTT
     */
    public function connect(): void
    {
        try {
            $this->client->connect($this->connectionSettings, true);
            Log::info('Conectado al broker MQTT exitosamente');
        } catch (MqttClientException $e) {
            Log::error('Error al conectar con MQTT broker: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Suscribirse a un tópico
     */
    public function subscribe(string $topic, callable $callback, int $qos = 0): void
    {
        try {
            $this->client->subscribe($topic, $callback, $qos);
            Log::info("Suscrito al tópico: {$topic}");
        } catch (MqttClientException $e) {
            Log::error("Error al suscribirse al tópico {$topic}: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Suscribirse al tópico de datos de sensores
     */
    public function subscribeToSensorData(): void
    {
        $topic = 'sensors/environment/data';

        $this->subscribe($topic, function (string $topic, string $message) {
            $this->handleSensorData($message);
        }, 1);
    }

    /**
     * Parsear timestamp desde diferentes formatos
     */
    private function parseTimestamp($timestamp)
    {
        if (!$timestamp) {
            return now();
        }

        // Si es un número (Unix timestamp), usarlo directamente
        if (is_numeric($timestamp)) {
            return now()->setTimestamp((int) $timestamp);
        }

        // Si es un string ISO 8601 o cualquier formato de fecha
        try {
            return \Carbon\Carbon::parse($timestamp);
        } catch (\Exception $e) {
            Log::warning('Error al parsear timestamp, usando hora actual', [
                'timestamp' => $timestamp,
                'error' => $e->getMessage(),
            ]);
            return now();
        }
    }

    /**
     * Procesar datos recibidos del sensor
     */
    private function handleSensorData(string $message): void
    {
        try {
            $data = json_decode($message, true);

            if (!$data) {
                Log::warning('Mensaje MQTT inválido recibido: ' . $message);
                return;
            }

            Log::info('Datos de sensor recibidos:', $data);

            // Validar datos mínimos requeridos
            if (!isset($data['temperature']) || !isset($data['humidity'])) {
                Log::warning('Datos de sensor incompletos:', $data);
                return;
            }

            // Verificar alertas
            $alerts = $this->sensorService->checkAlerts($data);

            // Preparar datos para almacenar
            $sensorData = [
                'temperature' => (float) $data['temperature'],
                'humidity' => (float) $data['humidity'],
                'air_quality' => isset($data['air_quality']) ? (float) $data['air_quality'] : null,
                'co2' => isset($data['co2']) ? (float) $data['co2'] : null,
                'timestamp' => $this->parseTimestamp($data['timestamp'] ?? null),
                'alert' => $alerts,
            ];

            // Guardar en base de datos
            $saved = $this->sensorService->createSensorData($sensorData);

            Log::info('Datos de sensor almacenados correctamente', ['id' => $saved->id]);

            // Disparar evento de broadcasting para tiempo real
            broadcast(new SensorDataReceived($saved));

            // Si hay alertas, registrarlas y disparar evento
            if (!empty($alerts)) {
                Log::warning('Alertas detectadas:', $alerts);
                broadcast(new AlertTriggered($saved, $alerts));
            }

        } catch (\Exception $e) {
            Log::error('Error al procesar datos del sensor: ' . $e->getMessage(), [
                'message' => $message,
                'exception' => $e,
            ]);
        }
    }

    /**
     * Publicar un mensaje en un tópico
     */
    public function publish(string $topic, string $message, int $qos = 0, bool $retain = false): void
    {
        try {
            $this->client->publish($topic, $message, $qos, $retain);
            Log::info("Mensaje publicado en {$topic}: {$message}");
        } catch (MqttClientException $e) {
            Log::error("Error al publicar en {$topic}: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Enviar comando al Arduino
     */
    public function sendCommandToArduino(array $command): void
    {
        $topic = 'sensors/environment/commands';
        $message = json_encode($command);

        $this->publish($topic, $message, 1);
    }

    /**
     * Loop para escuchar mensajes
     */
    public function loop(bool $blocking = false, bool $allowSleep = true): void
    {
        try {
            $this->client->loop($blocking, $allowSleep);
        } catch (MqttClientException $e) {
            Log::error('Error en el loop MQTT: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Desconectar del broker
     */
    public function disconnect(): void
    {
        try {
            $this->client->disconnect();
            Log::info('Desconectado del broker MQTT');
        } catch (MqttClientException $e) {
            Log::error('Error al desconectar del broker MQTT: ' . $e->getMessage());
        }
    }

    /**
     * Interrumpir el loop
     */
    public function interrupt(): void
    {
        try {
            $this->client->interrupt();
            Log::info('Loop MQTT interrumpido');
        } catch (MqttClientException $e) {
            Log::error('Error al interrumpir el loop MQTT: ' . $e->getMessage());
        }
    }
}
