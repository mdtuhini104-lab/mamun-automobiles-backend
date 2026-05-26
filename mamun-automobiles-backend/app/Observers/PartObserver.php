<?php

namespace App\Observers;

use App\Models\Part;
use App\Notifications\LowStockNotification;
use App\Services\NotificationService;

class PartObserver
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Handle the Part "updated" event.
     */
    public function updated(Part $part): void
    {
        if ($part->wasChanged('stock_quantity')) {
            if ($part->stock_quantity <= $part->low_stock_threshold) {
                $this->notificationService->sendToAllAdmins(new LowStockNotification($part));
            }
        }
    }
}
