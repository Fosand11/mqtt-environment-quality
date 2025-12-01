<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SensorDataResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'temperature' => [
                'value' => $this->temperature,
                'unit' => '°C',
            ],
            'humidity' => [
                'value' => $this->humidity,
                'unit' => '%',
            ],
            'air_quality' => [
                'value' => $this->air_quality,
                'status' => $this->getAirQualityStatus($this->air_quality),
            ],
            'timestamp' => $this->timestamp,
            'alerts' => $this->alert ?? [],
            'has_alerts' => !empty($this->alert),
            'created_at' => $this->created_at,
        ];
    }

    /**
     * Determinar el estado de calidad del aire
     */
    private function getAirQualityStatus(?float $value): string
    {
        if ($value === null) {
            return 'unknown';
        }

        return match (true) {
            $value >= 80 => 'excellent',
            $value >= 60 => 'good',
            $value >= 40 => 'moderate',
            $value >= 20 => 'poor',
            default => 'very_poor',
        };
    }
}
