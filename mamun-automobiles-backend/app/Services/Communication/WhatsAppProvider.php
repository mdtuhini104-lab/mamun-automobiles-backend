<?php

namespace App\Services\Communication;

use App\Services\WhatsappGatewayService;

class WhatsAppProvider implements CommunicationProviderInterface
{
    protected $service;

    public function __construct(WhatsappGatewayService $service)
    {
        $this->service = $service;
    }

    public function send(string $recipient, string $message): bool
    {
        try {
            $this->service->send($recipient, $message);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
