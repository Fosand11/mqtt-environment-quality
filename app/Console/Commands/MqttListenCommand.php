<?php

namespace App\Console\Commands;

use App\Services\MqttService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class MqttListenCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mqtt:listen
                            {--timeout=0 : Tiempo en segundos antes de desconectar (0 = infinito)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Escuchar mensajes MQTT del broker y procesarlos';

    /**
     * Execute the console command.
     */
    public function handle(MqttService $mqttService): int
    {
        $this->info('🚀 Iniciando listener MQTT...');

        try {
            // Conectar al broker
            $this->info('📡 Conectando al broker MQTT...');
            $mqttService->connect();
            $this->info('✅ Conectado exitosamente');

            // Suscribirse a los tópicos
            $this->info('📥 Suscribiéndose a tópicos...');
            $mqttService->subscribeToSensorData();
            $this->info('✅ Suscrito a: sensors/environment/data');

            $timeout = (int) $this->option('timeout');
            $this->info("⏳ Escuchando mensajes" . ($timeout > 0 ? " por {$timeout} segundos" : " indefinidamente") . "...");
            $this->info('Presiona Ctrl+C para detener');

            // Loop de escucha
            $startTime = time();
            $loopCount = 0;
            $lastHeartbeat = time();

            while (true) {
                // Loop no bloqueante - permite ping/pong automático
                $mqttService->loop(true, true);
                $loopCount++;

                // Verificar timeout
                if ($timeout > 0 && (time() - $startTime) >= $timeout) {
                    $this->info("\n⏱️  Timeout alcanzado");
                    break;
                }

                // Mostrar heartbeat cada 30 segundos
                if ((time() - $lastHeartbeat) >= 30) {
                    $elapsed = time() - $startTime;
                    $this->line("💓 Heartbeat - Tiempo activo: {$elapsed}s");
                    $lastHeartbeat = time();
                }

                // Pequeño delay para evitar 100% CPU
                usleep(100000); // 100ms
            }

            // Desconectar
            $mqttService->disconnect();
            $this->info('👋 Desconectado del broker MQTT');

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('❌ Error: ' . $e->getMessage());
            Log::error('Error en MQTT listener: ' . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString(),
            ]);

            // Intentar desconectar si es posible
            try {
                $mqttService->disconnect();
            } catch (\Exception $disconnectException) {
                Log::warning('Error al desconectar: ' . $disconnectException->getMessage());
            }

            // Si el error es de conexión, intentar reconectar
            if (str_contains($e->getMessage(), 'socket') ||
                str_contains($e->getMessage(), 'ping') ||
                str_contains($e->getMessage(), 'connection')) {

                $this->warn('🔄 Detectado error de conexión - Reintentando en 5 segundos...');
                sleep(5);

                // Reiniciar el comando
                return $this->call('mqtt:listen', ['--timeout' => $this->option('timeout')]);
            }

            return Command::FAILURE;
        }
    }
}
