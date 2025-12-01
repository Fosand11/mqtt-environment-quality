<?php

namespace App\Http\Controllers\Api\Metrics;

use App\Http\Controllers\Controller;
use App\Services\SensorService;
use Illuminate\Http\JsonResponse;

class TemperatureController extends Controller
{
    public function __construct(
        private readonly SensorService $sensorService
    ) {}

    /**
     * Obtener estadísticas de temperatura
     */
    public function __invoke(): JsonResponse
    {
        $stats = $this->sensorService->getTemperatureStats();

        return response()->json([
            'data' => $stats,
            'status' => 'success',
            'message' => __('messages.data_retrieved'),
        ]);
    }
}
