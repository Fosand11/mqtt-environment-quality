<?php

namespace App\Http\Controllers\Api\Metrics;

use App\Http\Controllers\Controller;
use App\Http\Requests\MetricsRequest;
use App\Services\SensorService;
use Illuminate\Http\JsonResponse;

class DailyController extends Controller
{
    public function __construct(
        private readonly SensorService $sensorService
    ) {}

    /**
     * Obtener métricas agrupadas por día
     */
    public function __invoke(MetricsRequest $request): JsonResponse
    {
        $days = $request->input('days', 7);
        $metrics = $this->sensorService->getDailyMetrics($days);

        return response()->json([
            'data' => $metrics['metrics'],
            'meta' => [
                'period' => $metrics['period'],
                'period_unit' => $metrics['period_unit'],
            ],
            'status' => 'success',
            'message' => __('messages.data_retrieved'),
        ]);
    }
}
