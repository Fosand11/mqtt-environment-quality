<?php

return [

    /*
    |--------------------------------------------------------------------------
    | MQTT Broker Configuration
    |--------------------------------------------------------------------------
    |
    | Configuración del broker MQTT para la conexión con los sensores IoT
    |
    */

    'broker' => [
        'host' => env('MQTT_BROKER_HOST', 'localhost'),
        'port' => env('MQTT_BROKER_PORT', 1883),
        'username' => env('MQTT_BROKER_USERNAME'),
        'password' => env('MQTT_BROKER_PASSWORD'),
    ],

    /*
    |--------------------------------------------------------------------------
    | MQTT Client Configuration
    |--------------------------------------------------------------------------
    |
    | Configuración del cliente MQTT de Laravel
    |
    */

    'client' => [
        'id' => env('MQTT_CLIENT_ID', 'laravel_mqtt_client'),
    ],

    /*
    |--------------------------------------------------------------------------
    | MQTT Topics
    |--------------------------------------------------------------------------
    |
    | Tópicos MQTT utilizados en la aplicación
    |
    */

    'topics' => [
        'sensor_data' => 'sensors/environment/data',
        'commands' => 'sensors/environment/commands',
        'alerts' => 'sensors/environment/alerts',
    ],

    /*
    |--------------------------------------------------------------------------
    | Alert Thresholds
    |--------------------------------------------------------------------------
    |
    | Umbrales para generar alertas automáticas
    |
    */

    'thresholds' => [
        'temperature' => [
            'warning' => 30,
            'critical' => 35,
        ],
        'humidity' => [
            'low' => 30,
            'high' => 80,
        ],
        'air_quality' => [
            'poor' => 50,
            'critical' => 20,
        ],
    ],

];
