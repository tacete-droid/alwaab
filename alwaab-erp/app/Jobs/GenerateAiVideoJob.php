<?php

namespace App\Jobs;

use App\Domain\AIStudio\Models\AiContent;
use App\Services\AiGenerationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Throwable;

class GenerateAiVideoJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 1;

    public int $timeout = 900;

    public function __construct(public string $contentId) {}

    public function handle(AiGenerationService $service): void
    {
        $content = AiContent::with('user')->findOrFail($this->contentId);

        try {
            $service->processVideo($content);
            $service->notifyCompleted($content->user, $content->fresh());
        } catch (Throwable $e) {
            $service->markFailed($content, $e->getMessage());
            throw $e;
        }
    }
}
