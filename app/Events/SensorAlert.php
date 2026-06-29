<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SensorAlert implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public string $sensorType;
    public float $value;
    public string $unit;
    public string $status;
    public string $deviceId;

    public function __construct(string $deviceId, string $sensorType, float $value, string $unit, string $status)
    {
        $this->deviceId = $deviceId;
        $this->sensorType = $sensorType;
        $this->value = $value;
        $this->unit = $unit;
        $this->status = $status;
        
    }

    public function broadcastOn(): array
    {
        return [
            new Channel('greenhouse-alerts'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'sensor.alert';
    }
}