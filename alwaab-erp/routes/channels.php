<?php

use App\Domain\Chat\Models\Conversation;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return $user->id === $id;
});

Broadcast::channel('conversation.{conversationId}', function ($user, string $conversationId) {
    return Conversation::query()
        ->where('id', $conversationId)
        ->whereHas('participants', fn ($q) => $q->where('users.id', $user->id))
        ->exists();
});
