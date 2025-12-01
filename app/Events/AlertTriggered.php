<?php

namespace App\Events;

use App\Models\SensorData;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AlertTriggered implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public SensorData $sensorData,
        public array $alerts
    ) {}

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('alerts'),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'alert.triggered';
    }

    /**
     * Get the data to broadcast.
     *
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'sensor_data_id' => $this->sensorData->id,
            'temperature' => $this->sensorData->temperature,
            'humidity' => $this->sensorData->humidity,
            'air_quality' => $this->sensorData->air_quality,
            'timestamp' => $this->sensorData->timestamp,
            'alerts' => $this->alerts,
            'alert_count' => count($this->alerts),
        ];
    }
}
