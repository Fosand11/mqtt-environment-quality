<?php

namespace App\Http\Controllers\Api\Sensors;

use App\Http\Controllers\Controller;
use App\Http\Resources\SensorDataResource;
use App\Services\SensorService;
use Illuminate\Http\JsonResponse;

class ShowController extends Controller
{
    public function __construct(
        private readonly SensorService $sensorService
    ) {}

    /**
     * Mostrar datos de un sensor específico
     */
    public function __invoke(string $id): JsonResponse|SensorDataResource
    {
        $sensor = $this->sensorService->findById($id);

        if (!$sensor) {
            return response()->json([
                'message' => __('messages.sensor.not_found'),
            ], 404);
        }

        return new SensorDataResource($sensor);
    }
}
