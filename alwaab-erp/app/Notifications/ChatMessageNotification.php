<?php

namespace App\Notifications;

use App\Domain\Chat\Models\Conversation;
use App\Domain\Chat\Models\Message;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ChatMessageNotification extends Notification
{
    use Queueable;

    public function __construct(
        private Message $message,
        private Conversation $conversation,
        private User $sender,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $locale = $notifiable->locale?->value ?? 'ar';

        return [
            'category' => 'chat',
            'sound' => 'chat',
            'priority' => 'normal',
            'icon' => '💬',
            'title' => $locale === 'ar'
                ? "رسالة من {$this->sender->name_ar}"
                : "Message from {$this->sender->name_en}",
            'body' => mb_strimwidth($this->message->body, 0, 120, '…'),
            'url' => '/chat?conversation_id='.$this->conversation->id,
            'conversation_id' => $this->conversation->id,
            'sender_id' => $this->sender->id,
        ];
    }
}
