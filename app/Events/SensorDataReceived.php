<?php

namespace App\Events;

use App\Models\SensorData;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SensorDataReceived implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public SensorData $sensorData
    ) {}

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('sensors'),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'sensor.data.received';
    }

    /**
     * Get the data to broadcast.
     *
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'id' => $this->sensorData->id,
            'temperature' => $this->sensorData->temperature,
            'humidity' => $this->sensorData->humidity,
            'air_quality' => $this->sensorData->air_quality,
            'timestamp' => $this->sensorData->timestamp,
            'alerts' => $this->sensorData->alert,
            'has_alerts' => !empty($this->sensorData->alert),
        ];
    }
}
