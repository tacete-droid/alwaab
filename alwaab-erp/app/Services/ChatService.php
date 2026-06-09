<?php

namespace App\Services;

use App\Domain\Chat\Models\Conversation;
use App\Domain\Chat\Models\Message;
use App\Enums\MessageType;
use App\Events\Chat\MessageSent;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ChatService
{
    public function getUserConversations(User $user): Collection
    {
        return Conversation::query()
            ->whereHas('participants', fn ($q) => $q->where('users.id', $user->id))
            ->with([
                'participants:id,name_ar,name_en,email',
                'messages' => fn ($q) => $q->latest()->limit(1)->with('sender:id,name_ar,name_en'),
            ])
            ->latest('updated_at')
            ->get()
            ->map(fn (Conversation $c) => $this->formatConversation($c, $user));
    }

    public function findOrCreateDirect(User $user, string $otherUserId): Conversation
    {
        $existing = Conversation::query()
            ->where('type', 'direct')
            ->whereHas('participants', fn ($q) => $q->where('users.id', $user->id))
            ->whereHas('participants', fn ($q) => $q->where('users.id', $otherUserId))
            ->first();

        if ($existing) {
            return $existing;
        }

        return DB::transaction(function () use ($user, $otherUserId) {
            $conversation = Conversation::create([
                'type' => 'direct',
                'created_by' => $user->id,
            ]);

            $conversation->participants()->attach([
                $user->id => ['id' => (string) Str::uuid()],
                $otherUserId => ['id' => (string) Str::uuid()],
            ]);

            return $conversation;
        });
    }

    public function getMessages(Conversation $conversation, User $user): Collection
    {
        abort_unless(
            $conversation->participants()->where('users.id', $user->id)->exists(),
            403
        );

        $conversation->participants()->updateExistingPivot($user->id, ['last_read_at' => now()]);

        return $conversation->messages()
            ->with('sender:id,name_ar,name_en')
            ->orderBy('created_at')
            ->get();
    }

    public function sendMessage(Conversation $conversation, User $user, string $body): Message
    {
        abort_unless(
            $conversation->participants()->where('users.id', $user->id)->exists(),
            403
        );

        $message = $conversation->messages()->create([
            'sender_id' => $user->id,
            'body' => $body,
            'type' => MessageType::Text,
        ]);

        $conversation->touch();

        $message->load('sender:id,name_ar,name_en');

        app(AppNotificationService::class)->notifyChatParticipants($message, $conversation, $user);

        event(new MessageSent($message, $conversation));

        return $message;
    }

    private function formatConversation(Conversation $conversation, User $user): array
    {
        $others = $conversation->participants->where('id', '!=', $user->id);
        $lastMessage = $conversation->messages->first();

        return [
            'id' => $conversation->id,
            'type' => $conversation->type,
            'name' => $conversation->name ?? $others->map(fn ($u) => $u->name)->join(', '),
            'participants' => $conversation->participants,
            'last_message' => $lastMessage ? [
                'body' => $lastMessage->body,
                'sender' => $lastMessage->sender?->name,
                'created_at' => $lastMessage->created_at,
            ] : null,
            'updated_at' => $conversation->updated_at,
        ];
    }
}
