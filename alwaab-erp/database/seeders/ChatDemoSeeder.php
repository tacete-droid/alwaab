<?php

namespace Database\Seeders;

use App\Domain\Chat\Models\Conversation;
use App\Enums\MessageType;
use App\Models\User;
use App\Services\ChatService;
use Illuminate\Database\Seeder;

class ChatDemoSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'admin@alwaab.ae')->first();
        $sales = User::where('email', 'sales@alwaab.ae')->first();

        if (! $admin || ! $sales || Conversation::exists()) {
            return;
        }

        $chat = app(ChatService::class);
        $conversation = $chat->findOrCreateDirect($admin, $sales->id);

        $conversation->messages()->create([
            'sender_id' => $admin->id,
            'body' => 'مرحباً سارة، هل تم متابعة عرض السعر لمشروع دبي مارينا؟',
            'type' => MessageType::Text,
        ]);

        $conversation->messages()->create([
            'sender_id' => $sales->id,
            'body' => 'نعم، العميل ينتظر الموافقة على البنود النهائية.',
            'type' => MessageType::Text,
        ]);
    }
}
