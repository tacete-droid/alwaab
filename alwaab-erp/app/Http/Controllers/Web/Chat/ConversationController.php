<?php

namespace App\Http\Controllers\Web\Chat;

use App\Domain\Chat\Models\Conversation;
use App\Http\Controllers\Controller;
use App\Http\Requests\Chat\SendMessageRequest;
use App\Http\Requests\Chat\StartConversationRequest;
use App\Models\User;
use App\Services\ChatService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ConversationController extends Controller
{
    public function __construct(private ChatService $chatService) {}

    public function index(Request $request): Response
    {
        $conversations = $this->chatService->getUserConversations($request->user());
        $activeId = $request->conversation_id ?? $conversations->first()['id'] ?? null;

        $messages = collect();
        if ($activeId) {
            $conversation = Conversation::find($activeId);
            if ($conversation) {
                $messages = $this->chatService->getMessages($conversation, $request->user());
            }
        }

        return Inertia::render('Chat/Index', [
            'conversations' => $conversations,
            'activeConversationId' => $activeId,
            'messages' => $messages,
            'users' => User::where('is_active', true)
                ->where('id', '!=', $request->user()->id)
                ->get(['id', 'name_ar', 'name_en', 'email']),
        ]);
    }

    public function store(StartConversationRequest $request): RedirectResponse
    {
        $conversation = $this->chatService->findOrCreateDirect(
            $request->user(),
            $request->validated('user_id'),
        );

        return redirect()->route('chat.index', ['conversation_id' => $conversation->id]);
    }

    public function send(SendMessageRequest $request, Conversation $conversation): RedirectResponse
    {
        $this->chatService->sendMessage(
            $conversation,
            $request->user(),
            $request->validated('body'),
        );

        return redirect()->route('chat.index', ['conversation_id' => $conversation->id]);
    }
}
