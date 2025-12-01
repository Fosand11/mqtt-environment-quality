<?php

use App\Http\Controllers\Api\Alerts\IndexController as AlertsIndexController;
use App\Http\Controllers\Api\Metrics\AirQualityController;
use App\Http\Controllers\Api\Metrics\DailyController;
use App\Http\Controllers\Api\Metrics\HourlyController;
use App\Http\Controllers\Api\Metrics\HumidityController;
use App\Http\Controllers\Api\Metrics\TemperatureController;
use App\Http\Controllers\Api\Sensors\DateRangeController;
use App\Http\Controllers\Api\Sensors\IndexController as SensorsIndexController;
use App\Http\Controllers\Api\Sensors\LatestController;
use App\Http\Controllers\Api\Sensors\ShowController;
use Illuminate\Support\Facades\Route;

/**
 * API Routes - Version 1
 *
 * Todas las rutas están bajo el prefijo /api/v1
 */

Route::prefix('v1')->group(function () {

    /**
     * Rutas de Sensores
     */
    Route::prefix('sensors')->name('sensors.')->group(function () {
        Route::get('/', SensorsIndexController::class)->name('index');
        Route::get('/latest', LatestController::class)->name('latest');
        Route::get('/{id}', ShowController::class)->name('show');
        Route::post('/date-range', DateRangeController::class)->name('date-range');
    });

    /**
     * Rutas de Métricas
     */
    Route::prefix('metrics')->name('metrics.')->group(function () {
        Route::get('/temperature', TemperatureController::class)->name('temperature');
        Route::get('/humidity', HumidityController::class)->name('humidity');
        Route::get('/air-quality', AirQualityController::class)->name('air-quality');
        Route::get('/hourly', HourlyController::class)->name('hourly');
        Route::get('/daily', DailyController::class)->name('daily');
    });

    /**
     * Rutas de Alertas
     */
    Route::prefix('alerts')->name('alerts.')->group(function () {
        Route::get('/', AlertsIndexController::class)->name('index');
    });
});
