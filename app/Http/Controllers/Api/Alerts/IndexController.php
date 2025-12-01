<?php

namespace App\Http\Controllers\Api\Alerts;

use App\Http\Controllers\Controller;
use App\Http\Resources\SensorDataResource;
use App\Services\SensorService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class IndexController extends Controller
{
    public function __construct(
        private readonly SensorService $sensorService
    ) {}

    /**
     * Obtener todas las alertas activas
     */
    public function __invoke(): AnonymousResourceCollection
    {
        $alerts = $this->sensorService->getActiveAlerts();

        return SensorDataResource::collection($alerts)
            ->additional([
                'meta' => [
                    'count' => $alerts->count(),
                ],
            ]);
    }
}
