<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class SystemEventNotification extends Notification
{
    use Queueable;

    public function __construct(private array $payload) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'category' => $this->payload['category'] ?? 'system',
            'sound' => $this->payload['sound'] ?? 'default',
            'priority' => $this->payload['priority'] ?? 'normal',
            'icon' => $this->payload['icon'] ?? '🔔',
            'title' => $this->payload['title'],
            'body' => $this->payload['body'],
            'url' => $this->payload['url'] ?? '/notifications',
        ];
    }
}
