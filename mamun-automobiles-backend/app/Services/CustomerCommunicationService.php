<?php

namespace App\Services;

use App\Models\Customer;
use App\Services\SmsGatewayService;
use App\Services\WhatsappGatewayService;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class CustomerCommunicationService
{
    protected SmsGatewayService $smsService;
    protected WhatsappGatewayService $whatsappService;

    public function __construct(SmsGatewayService $smsService, WhatsappGatewayService $whatsappService)
    {
        $this->smsService = $smsService;
        $this->whatsappService = $whatsappService;
    }

    /**
     * Send automated customer message with multi-channel fallback support.
     * 
     * @param Customer $customer Target customer
     * @param string $message Main message body
     * @param string $subject Subject line for email
     * @param array $channels Ordered channels to try
     * @return array Results of the notification delivery attempts
     */
    public function sendCustomerNotification(Customer $customer, string $message, string $subject = 'Mamun Automobiles Update', array $channels = ['whatsapp', 'sms', 'email']): array
    {
        $results = [];
        $delivered = false;

        foreach ($channels as $channel) {
            if ($delivered) {
                $results[$channel] = 'skipped (delivered via higher priority channel)';
                continue;
            }

            try {
                switch (strtolower($channel)) {
                    case 'whatsapp':
                        if (empty($customer->phone)) {
                            throw new \Exception("Customer has no phone number");
                        }
                        $results['whatsapp'] = $this->whatsappService->send($customer->phone, $message);
                        $delivered = true;
                        break;

                    case 'sms':
                        if (empty($customer->phone)) {
                            throw new \Exception("Customer has no phone number");
                        }
                        $results['sms'] = $this->smsService->send($customer->phone, $message);
                        $delivered = true;
                        break;

                    case 'email':
                        if (empty($customer->email)) {
                            throw new \Exception("Customer has no email address");
                        }
                        // Send simple fallback email using Laravel Mail facade
                        Mail::raw($message, function ($mail) use ($customer, $subject) {
                            $mail->to($customer->email)
                                 ->subject($subject);
                        });
                        $results['email'] = "Email successfully sent to {$customer->email}";
                        $delivered = true;
                        break;
                }
            } catch (\Exception $e) {
                Log::warning("Notification failed on channel {$channel} for Customer {$customer->id}: " . $e->getMessage());
                $results[$channel] = "failed: " . $e->getMessage();
            }
        }

        // Record customer communication log in DB
        try {
            \App\Models\CustomerActivityLog::create([
                'customer_id' => $customer->id,
                'activity_type' => 'communication',
                'description' => "Message dispatched. Channels tried: " . json_encode($results),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent()
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to log customer communication activity: " . $e->getMessage());
        }

        return [
            'success' => $delivered,
            'results' => $results,
        ];
    }
}
