<?php

namespace App\Events;

use App\Models\User;
use App\Models\WorkOrder;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TechnicianAssigned implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $workOrder;
    public $technician;

    public function __construct(WorkOrder $workOrder, User $technician)
    {
        $this->workOrder = $workOrder;
        $this->technician = $technician;
    }

    public function broadcastOn(): array
    {
        return [
            new Channel('workshop-updates'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'technician.assigned';
    }
}
