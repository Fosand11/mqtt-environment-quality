<?php

namespace App\Http\Controllers\Api\Metrics;

use App\Http\Controllers\Controller;
use App\Http\Requests\MetricsRequest;
use App\Services\SensorService;
use Illuminate\Http\JsonResponse;

class HourlyController extends Controller
{
    public function __construct(
        private readonly SensorService $sensorService
    ) {}

    /**
     * Obtener métricas agrupadas por hora
     */
    public function __invoke(MetricsRequest $request): JsonResponse
    {
        $hours = $request->input('hours', 24);
        $metrics = $this->sensorService->getHourlyMetrics($hours);

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
