<?php

namespace App\Notifications;

use App\Domain\Notifications\Models\SystemDirective;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class SystemDirectiveNotification extends Notification
{
    use Queueable;

    public function __construct(private SystemDirective $directive) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $locale = $notifiable->locale?->value ?? 'ar';
        $urgent = $this->directive->priority === 'urgent';

        return [
            'category' => 'directive',
            'sound' => $urgent ? 'alert' : 'default',
            'priority' => $this->directive->priority,
            'icon' => $urgent ? '🚨' : '📢',
            'title' => $locale === 'ar' ? $this->directive->title_ar : $this->directive->title_en,
            'body' => $locale === 'ar' ? $this->directive->body_ar : $this->directive->body_en,
            'url' => '/notifications',
            'directive_id' => $this->directive->id,
        ];
    }
}
