<?php

namespace App\Services;

use App\Services\Communication\CommunicationManager;

class SmsWhatsappNotificationCenter
{
    protected $communicationManager;

    public function __construct(CommunicationManager $communicationManager)
    {
        $this->communicationManager = $communicationManager;
    }

    /**
     * Enqueue a communication dispatch into the logs queue.
     */
    public function queueAlert(string $phone, string $emailAddr, string $message, ?int $tenantId = null): int
    {
        return $this->communicationManager->enqueueNotification($phone, $emailAddr, $message, $tenantId);
    }

    /**
     * Process the dispatch, executing fallback pathways in sequence.
     */
    public function processFallbackDispatch(int $logId, ?string $emailAddr = null): bool
    {
        return $this->communicationManager->dispatchNotification($logId, $emailAddr);
    }
}
