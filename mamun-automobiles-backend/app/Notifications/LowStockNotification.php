<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use App\Models\Part;

class LowStockNotification extends Notification
{
    use Queueable;

    protected $part;

    public function __construct(Part $part)
    {
        $this->part = $part;
    }

    public function via($notifiable)
    {
        return ['database', 'mail'];
    }

    public function toMail($notifiable)
    {
        return (new \App\Mail\LowStockMail($this->part))
            ->to($notifiable->email);
    }

    public function toArray($notifiable)
    {
        return [
            'title' => 'Low Stock Alert',
            'message' => "Part {$this->part->name} is low on stock. Current quantity: {$this->part->stock_quantity}",
            'part_id' => $this->part->id,
            'type' => 'low_stock',
        ];
    }
}
