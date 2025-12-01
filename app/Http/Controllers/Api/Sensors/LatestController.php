<?php

namespace App\Http\Controllers\Api\Sensors;

use App\Http\Controllers\Controller;
use App\Http\Resources\SensorDataResource;
use App\Services\SensorService;
use Illuminate\Http\JsonResponse;

class LatestController extends Controller
{
    public function __construct(
        private readonly SensorService $sensorService
    ) {}

    /**
     * Obtener los datos más recientes del sensor
     */
    public function __invoke(): JsonResponse|SensorDataResource
    {
        $latestData = $this->sensorService->getLatestData();

        if (!$latestData) {
            return response()->json([
                'message' => __('messages.sensor.no_data'),
            ], 404);
        }

        return new SensorDataResource($latestData);
    }
}
