<?php

namespace App\Http\Controllers\Api\Sensors;

use App\Http\Controllers\Controller;
use App\Http\Requests\DateRangeRequest;
use App\Http\Resources\SensorDataResource;
use App\Services\SensorService;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class DateRangeController extends Controller
{
    public function __construct(
        private readonly SensorService $sensorService
    ) {}

    /**
     * Obtener datos de sensores en un rango de fechas
     */
    public function __invoke(DateRangeRequest $request): AnonymousResourceCollection
    {
        $validated = $request->validated();

        $sensors = $this->sensorService->getByDateRange(
            Carbon::parse($validated['start_date']),
            Carbon::parse($validated['end_date'])
        );

        return SensorDataResource::collection($sensors)
            ->additional([
                'meta' => [
                    'count' => $sensors->count(),
                    'start_date' => $validated['start_date'],
                    'end_date' => $validated['end_date'],
                ],
            ]);
    }
}
