<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class JobMoved implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $jobCardId;
    public $oldStage;
    public $newStage;
    public $changedBy;
    public $notes;

    public function __construct($jobCardId, $oldStage, $newStage, $changedBy, $notes = '')
    {
        $this->jobCardId = $jobCardId;
        $this->oldStage = $oldStage;
        $this->newStage = $newStage;
        $this->changedBy = $changedBy;
        $this->notes = $notes;
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('workshop')
        ];
    }

    /**
     * Broadcast alias.
     */
    public function broadcastAs(): string
    {
        return 'job.moved';
    }
}
