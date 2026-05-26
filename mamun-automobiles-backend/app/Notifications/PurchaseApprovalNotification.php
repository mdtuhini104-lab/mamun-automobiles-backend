<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use App\Models\Purchase;

class PurchaseApprovalNotification extends Notification
{
    use Queueable;

    protected $purchase;
    protected $status;

    public function __construct(Purchase $purchase, string $status)
    {
        $this->purchase = $purchase;
        $this->status = $status;
    }

    public function via($notifiable)
    {
        return ['database', 'mail'];
    }

    public function toMail($notifiable)
    {
        return (new \App\Mail\PurchaseApprovalMail($this->purchase))
            ->to($notifiable->email);
    }

    public function toArray($notifiable)
    {
        $action = $this->status === 'approved' ? 'approved' : 'rejected';
        return [
            'title' => "Purchase {$action}",
            'message' => "Purchase {$this->purchase->purchase_no} has been {$action}.",
            'purchase_id' => $this->purchase->id,
            'type' => "purchase_{$action}",
        ];
    }
}
