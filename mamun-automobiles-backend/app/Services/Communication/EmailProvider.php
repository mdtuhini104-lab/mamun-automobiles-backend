<?php

namespace App\Services\Communication;

use Illuminate\Support\Facades\Mail;

class EmailProvider implements CommunicationProviderInterface
{
    public function send(string $recipient, string $message): bool
    {
        try {
            Mail::raw($message, function ($mail) use ($recipient) {
                $mail->to($recipient)
                    ->subject('Mamun Automobiles ERP Notification');
            });
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
