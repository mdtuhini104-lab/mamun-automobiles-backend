<?php

namespace App\Services;

class SmsGatewayService
{
    public function send($phone, $message)
    {
        // Integration with BulkSMSBD, Twilio, SSL Wireless, etc.
        // For demonstration, we simulate success
        if (empty($phone)) {
            throw new \Exception("Phone number is required");
        }
        
        return "SMS successfully sent to $phone via Provider";
    }
}
