<?php

namespace App\Services;

use App\Domain\Chat\Models\Conversation;
use App\Domain\Chat\Models\Message;
use App\Domain\Notifications\Models\SystemDirective;
use App\Models\User;
use App\Notifications\ChatMessageNotification;
use App\Notifications\SystemDirectiveNotification;
use App\Notifications\SystemEventNotification;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Collection;

class AppNotificationService
{
    public function notifyChatParticipants(Message $message, Conversation $conversation, User $sender): void
    {
        $conversation->loadMissing('participants');

        foreach ($conversation->participants as $participant) {
            if ($participant->id === $sender->id) {
                continue;
            }

            $participant->notify(new ChatMessageNotification($message, $conversation, $sender));
        }
    }

    public function sendDirective(SystemDirective $directive): int
    {
        $recipients = $this->resolveDirectiveRecipients($directive);
        $notification = new SystemDirectiveNotification($directive);

        foreach ($recipients as $user) {
            $user->notify($notification);
        }

        return $recipients->count();
    }

    public function notifyUser(User $user, array $payload): void
    {
        $user->notify(new SystemEventNotification($payload));
    }

    public function notifyUsers(Collection $users, array $payload): void
    {
        $notification = new SystemEventNotification($payload);

        foreach ($users as $user) {
            $user->notify($notification);
        }
    }

    public function unreadCount(User $user): int
    {
        return $user->unreadNotifications()->count();
    }

    public function poll(User $user, ?string $since = null): array
    {
        $query = $user->notifications()->latest();

        if ($since) {
            $query->where('created_at', '>', $since);
        } else {
            $query->limit(20);
        }

        $notifications = $query->get()->map(fn (DatabaseNotification $n) => $this->format($n));

        return [
            'notifications' => $notifications,
            'unread_count' => $this->unreadCount($user),
        ];
    }

    public function list(User $user, int $perPage = 30)
    {
        return $user->notifications()
            ->latest()
            ->paginate($perPage)
            ->through(fn (DatabaseNotification $n) => $this->format($n));
    }

    public function markRead(User $user, string $notificationId): void
    {
        $user->notifications()->where('id', $notificationId)->update(['read_at' => now()]);
    }

    public function markAllRead(User $user): void
    {
        $user->unreadNotifications()->update(['read_at' => now()]);
    }

    private function resolveDirectiveRecipients(SystemDirective $directive): Collection
    {
        return match ($directive->target) {
            'role' => User::role($directive->target_role)->where('is_active', true)->get(),
            'user' => User::where('id', $directive->target_user_id)->where('is_active', true)->get(),
            default => User::where('is_active', true)->get(),
        };
    }

    private function format(DatabaseNotification $notification): array
    {
        $data = $notification->data;

        return [
            'id' => $notification->id,
            'category' => $data['category'] ?? 'system',
            'sound' => $data['sound'] ?? 'default',
            'priority' => $data['priority'] ?? 'normal',
            'icon' => $data['icon'] ?? '🔔',
            'title' => $data['title'] ?? '',
            'body' => $data['body'] ?? '',
            'url' => $data['url'] ?? '/notifications',
            'read_at' => $notification->read_at?->toIso8601String(),
            'created_at' => $notification->created_at->toIso8601String(),
        ];
    }
}
