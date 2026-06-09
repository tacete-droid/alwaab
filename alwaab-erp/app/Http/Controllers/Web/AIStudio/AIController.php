<?php

namespace App\Http\Controllers\Web\AIStudio;

use App\Domain\AIStudio\Models\AiContent;
use App\Http\Controllers\Controller;
use App\Http\Requests\AIStudio\GenerateAiContentRequest;
use App\Http\Requests\AIStudio\TopUpCreditsRequest;
use App\Models\User;
use App\Services\AiCreditsService;
use App\Services\AiGenerationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use RuntimeException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AIController extends Controller
{
    public function __construct(
        private AiGenerationService $generator,
        private AiCreditsService $credits,
    ) {}

    public function index(Request $request): Response
    {
        $history = AiContent::query()
            ->where('user_id', $request->user()->id)
            ->latest()
            ->paginate(15)
            ->through(fn (AiContent $item) => [
                'id' => $item->id,
                'type' => $item->type->value,
                'prompt' => $item->prompt,
                'status' => $item->status->value,
                'file_path' => $item->file_path,
                'download_url' => $item->file_path ? Storage::disk('public')->url($item->file_path) : null,
                'output' => $item->metadata['output'] ?? null,
                'error' => $item->metadata['error'] ?? null,
                'reference_url' => $item->metadata['reference_url'] ?? null,
                'reference_type' => $item->metadata['reference_type'] ?? null,
                'reference_name' => $item->metadata['reference_name'] ?? null,
                'created_at' => $item->created_at->toIso8601String(),
            ]);

        return Inertia::render('AIStudio/Index', [
            'history' => $history,
            'credits' => $this->credits->balance($request->user()),
            'creditCosts' => [
                'text' => 0,
                'image' => 1,
                'video' => 5,
            ],
            'canManageCredits' => $request->user()->can('users.manage'),
            'users' => $request->user()->can('users.manage')
                ? User::where('is_active', true)->orderBy('name_en')->get(['id', 'name_ar', 'name_en', 'email', 'ai_credits'])
                : [],
        ]);
    }

    public function generateText(GenerateAiContentRequest $request): RedirectResponse
    {
        try {
            $this->generator->generateText($request->user(), $request->validated('prompt'));
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', __('ai_studio.text_generated'));
    }

    public function generateImage(GenerateAiContentRequest $request): RedirectResponse
    {
        try {
            $this->generator->queueImage(
                $request->user(),
                $request->validated('prompt'),
                $request->file('reference_file'),
            );
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', __('ai_studio.queued_image'));
    }

    public function generateVideo(GenerateAiContentRequest $request): RedirectResponse
    {
        try {
            $this->generator->queueVideo(
                $request->user(),
                $request->validated('prompt'),
                $request->file('reference_file'),
            );
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', __('ai_studio.queued_video'));
    }

    public function download(Request $request, AiContent $content): StreamedResponse
    {
        abort_unless($content->user_id === $request->user()->id, 403);
        abort_unless($content->file_path && Storage::disk('public')->exists($content->file_path), 404);

        return Storage::disk('public')->download($content->file_path);
    }

    public function topUpCredits(TopUpCreditsRequest $request): RedirectResponse
    {
        $user = User::findOrFail($request->validated('user_id'));
        $this->credits->topUp($user, (int) $request->validated('amount'));

        return back()->with('success', __('ai_studio.credits_topped_up', ['name' => $user->name]));
    }
}
