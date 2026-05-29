<?php

namespace App\Events;

use App\Models\WorkOrderConsumption;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AdditionalConsumptionAdded implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $consumption;

    public function __construct(WorkOrderConsumption $consumption)
    {
        $this->consumption = $consumption;
    }

    public function broadcastOn(): array
    {
        return [
            new Channel('workshop-updates'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'consumption.added';
    }
}
