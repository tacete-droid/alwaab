<?php

namespace App\Jobs;

use App\Domain\AIStudio\Models\AiContent;
use App\Services\AiGenerationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Throwable;

class GenerateAiImageJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 2;

    public int $timeout = 300;

    public function __construct(public string $contentId) {}

    public function handle(AiGenerationService $service): void
    {
        $content = AiContent::with('user')->findOrFail($this->contentId);

        try {
            $service->processImage($content);
            $service->notifyCompleted($content->user, $content->fresh());
        } catch (Throwable $e) {
            $service->markFailed($content, $e->getMessage());
            throw $e;
        }
    }
}
