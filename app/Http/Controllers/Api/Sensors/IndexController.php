<?php

namespace App\Http\Controllers\Api\Sensors;

use App\Http\Controllers\Controller;
use App\Http\Resources\SensorDataResource;
use App\Services\SensorService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class IndexController extends Controller
{
    public function __construct(
        private readonly SensorService $sensorService
    ) {}

    /**
     * Listar todos los datos de sensores con paginación
     */
    public function __invoke(Request $request): AnonymousResourceCollection
    {
        $perPage = $request->input('per_page', 15);
        $sensors = $this->sensorService->getAllSensors($perPage);

        return SensorDataResource::collection($sensors);
    }
}
