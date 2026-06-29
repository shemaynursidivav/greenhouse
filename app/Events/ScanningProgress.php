<?php

namespace App\Events;

use App\Models\ScanningSession;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ScanningProgress implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public ScanningSession $session;

    public function __construct(ScanningSession $session)
    {
        $this->session = $session;
    }

    public function broadcastOn(): array
    {
        return [
            new Channel('scanning-' . $this->session->id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'scanning.progress';
    }

    public function broadcastWith(): array
    {
        return [
            'session_id' => $this->session->id,
            'status'     => $this->session->status,
            'progress'   => $this->session->progress,
        ];
    }
}