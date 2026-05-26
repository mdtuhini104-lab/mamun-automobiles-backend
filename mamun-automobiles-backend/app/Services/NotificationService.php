<?php

namespace App\Services;

use App\Models\NotificationLog;
use App\Models\NotificationTemplate;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    protected $smsService;
    protected $whatsappService;
    protected $templateService;

    public function __construct(
        SmsGatewayService $smsService,
        WhatsappGatewayService $whatsappService,
        NotificationTemplateService $templateService
    ) {
        $this->smsService = $smsService;
        $this->whatsappService = $whatsappService;
        $this->templateService = $templateService;
    }

    public function sendEventNotification($eventKey, $recipient, $data = [], $customerId = null)
    {
        $templates = NotificationTemplate::where('event_key', $eventKey)->where('is_active', true)->get();

        foreach ($templates as $template) {
            $message = $this->templateService->render($template->message_body, $data);
            
            if ($template->channel === 'sms') {
                $this->sendSms($recipient, $message, $customerId);
            } elseif ($template->channel === 'whatsapp') {
                $this->sendWhatsapp($recipient, $message, $customerId);
            }
        }
    }

    public function sendSms($phone, $message, $customerId = null)
    {
        try {
            $response = $this->smsService->send($phone, $message);
            $this->logNotification('sms', $phone, $message, 'success', $response, $customerId);
        } catch (\Exception $e) {
            $this->logNotification('sms', $phone, $message, 'failed', $e->getMessage(), $customerId, 'danger');
        }
    }

    public function sendWhatsapp($phone, $message, $customerId = null)
    {
        try {
            $response = $this->whatsappService->send($phone, $message);
            $this->logNotification('whatsapp', $phone, $message, 'success', $response, $customerId);
        } catch (\Exception $e) {
            $this->logNotification('whatsapp', $phone, $message, 'failed', $e->getMessage(), $customerId, 'danger');
        }
    }

    protected function logNotification($channel, $recipient, $message, $status, $response, $customerId = null, $severity = 'info')
    {
        NotificationLog::create([
            'customer_id' => $customerId,
            'channel' => $channel,
            'recipient' => $recipient,
            'message' => $message,
            'status' => $status,
            'provider_response' => is_string($response) ? $response : json_encode($response),
            'severity' => $severity,
            'sent_at' => now(),
        ]);
    }
}
