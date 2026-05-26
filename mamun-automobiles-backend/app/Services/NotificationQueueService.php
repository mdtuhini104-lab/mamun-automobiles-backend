<?php

namespace App\Services;

class NotificationQueueService
{
    public function enqueue($eventKey, $recipient, $data = [], $customerId = null)
    {
        // Usually we would dispatch a Laravel Job here:
        // dispatch(new \App\Jobs\SendNotificationJob($eventKey, $recipient, $data, $customerId));
        
        // Simulating immediate execution for now
        $service = app(NotificationService::class);
        $service->sendEventNotification($eventKey, $recipient, $data, $customerId);
    }
}
