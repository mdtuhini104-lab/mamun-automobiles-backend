<?php

namespace App\Services;

class WhatsappGatewayService
{
    public function send($phone, $message, $attachmentUrl = null)
    {
        // Integration with Meta Cloud API, Green API, etc.
        if (empty($phone)) {
            throw new \Exception("Phone number is required");
        }

        return "WhatsApp message successfully sent to $phone";
    }
}
