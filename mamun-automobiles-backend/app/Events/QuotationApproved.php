<?php

namespace App\Events;

use App\Models\Quotation;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class QuotationApproved implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $quotation;

    public function __construct(Quotation $quotation)
    {
        $this->quotation = $quotation;
    }

    public function broadcastOn(): array
    {
        return [
            new Channel('workshop-updates'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'quotation.approved';
    }
}
