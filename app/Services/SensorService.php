<?php

namespace App\Services;

use App\Models\SensorData;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class SensorService
{
    /**
     * Obtener datos de sensores con paginación
     */
    public function getAllSensors(int $perPage = 15): LengthAwarePaginator
    {
        return SensorData::orderBy('timestamp', 'desc')
            ->paginate($perPage);
    }

    /**
     * Obtener el dato más reciente del sensor
     */
    public function getLatestData(): ?SensorData
    {
        return SensorData::orderBy('timestamp', 'desc')->first();
    }

    /**
     * Encontrar sensor por ID
     */
    public function findById(string $id): ?SensorData
    {
        return SensorData::find($id);
    }

    /**
     * Obtener datos por rango de fechas
     */
    public function getByDateRange(Carbon $startDate, Carbon $endDate): Collection
    {
        return SensorData::whereBetween('timestamp', [$startDate, $endDate])
            ->orderBy('timestamp', 'desc')
            ->get();
    }

    /**
     * Calcular estadísticas de temperatura
     */
    public function getTemperatureStats(): array
    {
        $latest = $this->getLatestData();
        $last24h = Carbon::now()->subDay();

        return [
            'current' => $latest?->temperature,
            'average' => round(SensorData::avg('temperature'), 2),
            'max' => round(SensorData::max('temperature'), 2),
            'min' => round(SensorData::min('temperature'), 2),
            'last_24h_avg' => round(
                SensorData::where('timestamp', '>=', $last24h)->avg('temperature'),
                2
            ),
        ];
    }

    /**
     * Calcular estadísticas de humedad
     */
    public function getHumidityStats(): array
    {
        $latest = $this->getLatestData();
        $last24h = Carbon::now()->subDay();

        return [
            'current' => $latest?->humidity,
            'average' => round(SensorData::avg('humidity'), 2),
            'max' => round(SensorData::max('humidity'), 2),
            'min' => round(SensorData::min('humidity'), 2),
            'last_24h_avg' => round(
                SensorData::where('timestamp', '>=', $last24h)->avg('humidity'),
                2
            ),
        ];
    }

    /**
     * Calcular estadísticas de calidad del aire
     */
    public function getAirQualityStats(): array
    {
        $latest = $this->getLatestData();
        $last24h = Carbon::now()->subDay();

        return [
            'current' => $latest?->air_quality,
            'average' => round(SensorData::avg('air_quality'), 2),
            'max' => round(SensorData::max('air_quality'), 2),
            'min' => round(SensorData::min('air_quality'), 2),
            'last_24h_avg' => round(
                SensorData::where('timestamp', '>=', $last24h)->avg('air_quality'),
                2
            ),
        ];
    }

    /**
     * Obtener métricas agrupadas por hora
     */
    public function getHourlyMetrics(int $hours = 24): array
    {
        $startTime = Carbon::now()->subHours($hours);

        $metrics = SensorData::where('timestamp', '>=', $startTime)
            ->orderBy('timestamp', 'asc')
            ->get()
            ->groupBy(function($item) {
                return Carbon::parse($item->timestamp)->format('Y-m-d H:00');
            })
            ->map(function($group) {
                return [
                    'hour' => $group->first()->timestamp,
                    'avg_temperature' => round($group->avg('temperature'), 2),
                    'avg_humidity' => round($group->avg('humidity'), 2),
                    'avg_air_quality' => round($group->avg('air_quality'), 2),
                    'count' => $group->count(),
                ];
            })
            ->values()
            ->toArray();

        return [
            'metrics' => $metrics,
            'period' => $hours,
            'period_unit' => 'hours',
        ];
    }

    /**
     * Obtener métricas agrupadas por día
     */
    public function getDailyMetrics(int $days = 7): array
    {
        $startTime = Carbon::now()->subDays($days);

        $metrics = SensorData::where('timestamp', '>=', $startTime)
            ->orderBy('timestamp', 'asc')
            ->get()
            ->groupBy(function($item) {
                return Carbon::parse($item->timestamp)->format('Y-m-d');
            })
            ->map(function($group) {
                return [
                    'date' => $group->first()->timestamp,
                    'avg_temperature' => round($group->avg('temperature'), 2),
                    'max_temperature' => round($group->max('temperature'), 2),
                    'min_temperature' => round($group->min('temperature'), 2),
                    'avg_humidity' => round($group->avg('humidity'), 2),
                    'avg_air_quality' => round($group->avg('air_quality'), 2),
                    'count' => $group->count(),
                ];
            })
            ->values()
            ->toArray();

        return [
            'metrics' => $metrics,
            'period' => $days,
            'period_unit' => 'days',
        ];
    }

    /**
     * Obtener alertas activas
     */
    public function getActiveAlerts(int $limit = 50): Collection
    {
        return SensorData::whereNotNull('alert')
            ->where('alert', '!=', [])
            ->orderBy('timestamp', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Crear nuevo registro de sensor
     */
    public function createSensorData(array $data): SensorData
    {
        return SensorData::create([
            'temperature' => $data['temperature'],
            'humidity' => $data['humidity'],
            'air_quality' => $data['air_quality'] ?? null,
            'timestamp' => $data['timestamp'] ?? now(),
            'alert' => $data['alert'] ?? [],
        ]);
    }

    /**
     * Validar y generar alertas según umbrales
     */
    public function checkAlerts(array $data): array
    {
        $alerts = [];

        // Umbral de temperatura (ejemplo: > 30°C)
        if (isset($data['temperature']) && $data['temperature'] > 30) {
            $alerts[] = [
                'type' => 'temperature',
                'severity' => 'warning',
                'message' => 'Temperatura alta detectada',
                'value' => $data['temperature'],
            ];
        }

        // Umbral de temperatura crítico (ejemplo: > 35°C)
        if (isset($data['temperature']) && $data['temperature'] > 35) {
            $alerts[] = [
                'type' => 'temperature',
                'severity' => 'critical',
                'message' => 'Temperatura crítica detectada',
                'value' => $data['temperature'],
            ];
        }

        // Umbral de humedad baja (ejemplo: < 30%)
        if (isset($data['humidity']) && $data['humidity'] < 30) {
            $alerts[] = [
                'type' => 'humidity',
                'severity' => 'warning',
                'message' => 'Humedad baja detectada',
                'value' => $data['humidity'],
            ];
        }

        // Umbral de humedad alta (ejemplo: > 80%)
        if (isset($data['humidity']) && $data['humidity'] > 80) {
            $alerts[] = [
                'type' => 'humidity',
                'severity' => 'warning',
                'message' => 'Humedad alta detectada',
                'value' => $data['humidity'],
            ];
        }

        // Calidad del aire baja (ejemplo: < 50)
        if (isset($data['air_quality']) && $data['air_quality'] < 50) {
            $alerts[] = [
                'type' => 'air_quality',
                'severity' => 'critical',
                'message' => 'Calidad del aire pobre',
                'value' => $data['air_quality'],
            ];
        }

        return $alerts;
    }
}
