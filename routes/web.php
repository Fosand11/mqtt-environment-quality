<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json([
        'message' => 'MQTT Environment Quality Monitoring API',
        'version' => '1.0',
        'endpoints' => [
            'sensors' => '/api/v1/sensors',
            'latest' => '/api/v1/sensors/latest',
            'metrics' => '/api/v1/metrics',
            'alerts' => '/api/v1/alerts',
        ],
        'documentation' => 'See README.md for full API documentation'
    ]);
});
