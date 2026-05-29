<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class SystemNotification extends Notification
{
    use Queueable;

    protected $title;
    protected $message;
    protected $type;
    protected $metadata;

    public function __construct(string $title, string $message, string $type, array $metadata = [])
    {
        $this->title = $title;
        $this->message = $message;
        $this->type = $type;
        $this->metadata = $metadata;
    }

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        return array_merge([
            'title' => $this->title,
            'message' => $this->message,
            'type' => $this->type,
        ], $this->metadata);
    }
}
