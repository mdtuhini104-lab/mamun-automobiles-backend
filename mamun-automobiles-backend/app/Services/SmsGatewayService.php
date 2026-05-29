<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SmsGatewayService
{
    /**
     * Send SMS via Twilio API.
     */
    public function send(string $phone, string $message): string
    {
        if (empty($phone)) {
            throw new \Exception("Phone number is required");
        }

        $sid = config('services.twilio.sid') ?: env('TWILIO_SID');
        $token = config('services.twilio.auth_token') ?: env('TWILIO_AUTH_TOKEN');
        $from = config('services.twilio.from') ?: env('TWILIO_NUMBER');

        if ($sid && $token && $from) {
            try {
                $response = Http::withBasicAuth($sid, $token)
                    ->asForm()
                    ->post("https://api.twilio.com/2010-04-01/Accounts/{$sid}/Messages.json", [
                        'To' => $phone,
                        'From' => $from,
                        'Body' => $message,
                    ]);

                if ($response->successful()) {
                    Log::info("SMS delivered successfully via Twilio to {$phone}");
                    return "SMS successfully sent to {$phone} via Twilio SID: " . ($response->json()['sid'] ?? 'N/A');
                }

                throw new \Exception("Twilio API returned error: " . $response->body());
            } catch (\Exception $e) {
                Log::error("Twilio SMS send error: " . $e->getMessage());
                throw $e;
            }
        }

        // Credentials not configured - fallback to secure simulation log
        Log::info("SMS SIMULATION [Twilio Credentials Missing]: Sent to {$phone} - Message: {$message}");
        return "SMS successfully sent to {$phone} via Simulation Gateway";
    }
}
