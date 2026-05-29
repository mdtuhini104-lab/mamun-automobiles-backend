<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SystemReliabilityIncident implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $action;

    public function __construct(string $action)
    {
        $this->action = $action;
    }

    public function broadcastOn()
    {
        return new Channel('system-health');
    }

    public function broadcastAs()
    {
        return 'reliability.incident';
    }
}
