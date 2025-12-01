<?php

namespace App\Http\Controllers\Api\Metrics;

use App\Http\Controllers\Controller;
use App\Services\SensorService;
use Illuminate\Http\JsonResponse;

class HumidityController extends Controller
{
    public function __construct(
        private readonly SensorService $sensorService
    ) {}

    /**
     * Obtener estadísticas de humedad
     */
    public function __invoke(): JsonResponse
    {
        $stats = $this->sensorService->getHumidityStats();

        return response()->json([
            'data' => $stats,
            'status' => 'success',
            'message' => __('messages.data_retrieved'),
        ]);
    }
}
