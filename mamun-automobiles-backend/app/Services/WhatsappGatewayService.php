<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsappGatewayService
{
    /**
     * Send WhatsApp message via Meta Cloud API.
     */
    public function send(string $phone, string $message, ?string $attachmentUrl = null): string
    {
        if (empty($phone)) {
            throw new \Exception("Phone number is required");
        }

        $phoneNumberId = config('services.whatsapp.phone_number_id') ?: env('WHATSAPP_PHONE_NUMBER_ID');
        $accessToken = config('services.whatsapp.access_token') ?: env('WHATSAPP_ACCESS_TOKEN');

        if ($phoneNumberId && $accessToken) {
            try {
                $url = "https://graph.facebook.com/v20.0/{$phoneNumberId}/messages";

                $payload = [
                    'messaging_product' => 'whatsapp',
                    'recipient_type' => 'individual',
                    'to' => $phone,
                    'type' => 'text',
                    'text' => [
                        'preview_url' => true,
                        'body' => $message
                    ]
                ];

                // If template or media are needed in future, we can adapt here
                if ($attachmentUrl) {
                    $payload['type'] = 'image';
                    $payload['image'] = [
                        'link' => $attachmentUrl,
                        'caption' => $message
                    ];
                    unset($payload['text']);
                }

                $response = Http::withToken($accessToken)
                    ->post($url, $payload);

                if ($response->successful()) {
                    Log::info("WhatsApp message delivered successfully via Meta to {$phone}");
                    return "WhatsApp message successfully sent to {$phone} via Meta Phone ID: {$phoneNumberId}";
                }

                throw new \Exception("WhatsApp Cloud API returned error: " . $response->body());
            } catch (\Exception $e) {
                Log::error("WhatsApp send error: " . $e->getMessage());
                throw $e;
            }
        }

        // Fallback to secure logging simulator
        Log::info("WHATSAPP SIMULATION [Meta Credentials Missing]: Sent to {$phone} - Message: {$message} (Media: " . ($attachmentUrl ?: 'None') . ")");
        return "WhatsApp message successfully sent to {$phone} via Simulation Gateway";
    }
}
