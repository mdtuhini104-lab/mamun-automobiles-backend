<?php

namespace App\Services\Communication;

use Illuminate\Support\Facades\DB;
use App\Services\Communication\WhatsAppProvider;
use App\Services\Communication\SmsProvider;
use App\Services\Communication\EmailProvider;
use App\Models\User;

class CommunicationManager
{
    protected $whatsApp;
    protected $sms;
    protected $email;

    public function __construct(WhatsAppProvider $whatsApp, SmsProvider $sms, EmailProvider $email)
    {
        $this->whatsApp = $whatsApp;
        $this->sms = $sms;
        $this->email = $email;
    }

    /**
     * Enqueue a communication dispatch into the logs queue database.
     */
    public function enqueueNotification(string $phone, string $emailAddr, string $message, ?int $tenantId = null): int
    {
        $tenantId = $tenantId ?? (auth()->check() ? auth()->user()->tenant_id : 1);

        return DB::table('communication_logs')->insertGetId([
            'tenant_id' => $tenantId,
            'recipient_phone' => $phone,
            'message_body' => $message,
            'channel' => 'whatsapp', // Starts on primary WhatsApp
            'status' => 'queued',
            'retry_count' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Dispatches the notification, executing fallbacks if failure is met.
     */
    public function dispatchNotification(int $logId, ?string $emailAddr = null): bool
    {
        $log = DB::table('communication_logs')->where('id', $logId)->first();
        if (!$log) {
            return false;
        }

        DB::table('communication_logs')->where('id', $logId)->update([
            'status' => 'dispatched',
            'updated_at' => now(),
        ]);

        // 1. Try WhatsApp
        $success = $this->whatsApp->send($log->recipient_phone, $log->message_body);
        if ($success) {
            DB::table('communication_logs')->where('id', $logId)->update([
                'channel' => 'whatsapp',
                'status' => 'delivered',
                'updated_at' => now(),
            ]);
            return true;
        }

        // 2. Fallback to SMS
        DB::table('communication_logs')->where('id', $logId)->update([
            'channel' => 'sms',
            'retry_count' => $log->retry_count + 1,
            'error_log' => 'WhatsApp failed. Retrying via SMS.',
            'updated_at' => now(),
        ]);

        $success = $this->sms->send($log->recipient_phone, $log->message_body);
        if ($success) {
            DB::table('communication_logs')->where('id', $logId)->update([
                'status' => 'delivered',
                'updated_at' => now(),
            ]);
            return true;
        }

        // 3. Fallback to Email if address provided
        if ($emailAddr) {
            DB::table('communication_logs')->where('id', $logId)->update([
                'channel' => 'email',
                'retry_count' => $log->retry_count + 2,
                'error_log' => 'SMS failed. Retrying via Email.',
                'updated_at' => now(),
            ]);

            $success = $this->email->send($emailAddr, $log->message_body);
            if ($success) {
                DB::table('communication_logs')->where('id', $logId)->update([
                    'status' => 'delivered',
                    'updated_at' => now(),
                ]);
                return true;
            }
        }

        // Complete failure
        DB::table('communication_logs')->where('id', $logId)->update([
            'status' => 'failed',
            'error_log' => 'WhatsApp, SMS and Email channels failed to dispatch.',
            'updated_at' => now(),
        ]);

        return false;
    }
}
