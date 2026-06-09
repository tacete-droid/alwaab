<?php

namespace App\Services;

use App\Domain\AIStudio\Models\AiContent;
use App\Enums\AiContentStatus;
use App\Enums\AiContentType;
use App\Jobs\GenerateAiImageJob;
use App\Jobs\GenerateAiVideoJob;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use OpenAI\Laravel\Facades\OpenAI;
use RuntimeException;

class AiGenerationService
{
    public function __construct(
        private AiPromptService $prompts,
        private AiCreditsService $credits,
        private AiReferenceService $references,
        private AppNotificationService $notifications,
    ) {}

    public function generateText(User $user, string $prompt): AiContent
    {
        $enhanced = $this->prompts->enhance($prompt, AiContentType::Text);

        $content = AiContent::create([
            'user_id' => $user->id,
            'type' => AiContentType::Text,
            'prompt' => $prompt,
            'status' => AiContentStatus::Processing,
            'metadata' => ['enhanced_prompt' => $enhanced],
        ]);

        try {
            $response = OpenAI::chat()->create([
                'model' => config('ai_studio.text_model'),
                'messages' => [
                    ['role' => 'system', 'content' => 'You are a helpful assistant for ALWAAB ERP employees. Respond clearly and professionally.'],
                    ['role' => 'user', 'content' => $enhanced],
                ],
            ]);

            $text = $response->choices[0]->message->content ?? '';
            $path = $this->storeText($user->id, $content->id, $text);

            $content->update([
                'status' => AiContentStatus::Completed,
                'file_path' => $path,
                'metadata' => array_merge($content->metadata ?? [], [
                    'output' => $text,
                    'model' => config('ai_studio.text_model'),
                    'tokens' => $response->usage->totalTokens ?? null,
                ]),
            ]);

            $this->notifyCompleted($user, $content);
        } catch (\Throwable $e) {
            $content->update([
                'status' => AiContentStatus::Failed,
                'metadata' => array_merge($content->metadata ?? [], ['error' => $e->getMessage()]),
            ]);

            throw $e;
        }

        return $content->fresh();
    }

    public function queueImage(User $user, string $prompt, ?UploadedFile $reference = null): AiContent
    {
        return $this->queueMedia($user, $prompt, AiContentType::Image, GenerateAiImageJob::class, $reference);
    }

    public function queueVideo(User $user, string $prompt, ?UploadedFile $reference = null): AiContent
    {
        return $this->queueMedia($user, $prompt, AiContentType::Video, GenerateAiVideoJob::class, $reference);
    }

    public function processImage(AiContent $content): void
    {
        $enhanced = $content->metadata['enhanced_prompt'] ?? $content->prompt;
        $enhanced = $this->references->applyToPrompt($content, $enhanced, AiContentType::Image);

        $response = OpenAI::images()->create([
            'model' => config('ai_studio.image_model'),
            'prompt' => $enhanced,
            'size' => config('ai_studio.image_size'),
            'n' => 1,
            'response_format' => 'url',
        ]);

        $url = $response->data[0]->url ?? null;

        if (! $url) {
            throw new RuntimeException('OpenAI returned no image URL.');
        }

        $imageData = file_get_contents($url);

        if ($imageData === false) {
            throw new RuntimeException('Failed to download generated image.');
        }

        $path = "ai-studio/{$content->user_id}/images/{$content->id}.png";
        Storage::disk('public')->put($path, $imageData);

        $content->update([
            'status' => AiContentStatus::Completed,
            'file_path' => $path,
            'metadata' => array_merge($content->metadata ?? [], [
                'model' => config('ai_studio.image_model'),
                'size' => config('ai_studio.image_size'),
                'final_prompt' => $enhanced,
            ]),
        ]);
    }

    public function processVideo(AiContent $content): void
    {
        $enhanced = $content->metadata['enhanced_prompt'] ?? $content->prompt;
        $enhanced = $this->references->applyToPrompt($content, $enhanced, AiContentType::Video);
        $apiKey = config('ai_studio.luma_api_key');

        if (! $apiKey) {
            throw new RuntimeException('LUMA_API_KEY is not configured.');
        }

        $payload = array_merge([
            'prompt' => $enhanced,
            'aspect_ratio' => '16:9',
            'resolution' => '1080p',
        ], $this->references->lumaPayloadExtras($content));

        $response = \Illuminate\Support\Facades\Http::withToken($apiKey)
            ->timeout(30)
            ->post(config('ai_studio.luma_api_url'), $payload);

        if (! $response->successful()) {
            throw new RuntimeException('Luma API error: '.$response->body());
        }

        $generationId = $response->json('id');
        $videoUrl = $this->pollLumaGeneration($apiKey, $generationId);

        $videoData = file_get_contents($videoUrl);

        if ($videoData === false) {
            throw new RuntimeException('Failed to download generated video.');
        }

        $path = "ai-studio/{$content->user_id}/videos/{$content->id}.mp4";
        Storage::disk('public')->put($path, $videoData);

        $content->update([
            'status' => AiContentStatus::Completed,
            'file_path' => $path,
            'metadata' => array_merge($content->metadata ?? [], [
                'provider' => 'luma',
                'generation_id' => $generationId,
                'resolution' => '1080p',
                'final_prompt' => $enhanced,
            ]),
        ]);
    }

    public function markFailed(AiContent $content, string $error, bool $refund = true): void
    {
        $content->update([
            'status' => AiContentStatus::Failed,
            'metadata' => array_merge($content->metadata ?? [], ['error' => $error]),
        ]);

        if ($refund) {
            $this->credits->refund($content->user, $content->type);
        }

        $this->notifyFailed($content->user, $content, $error);
    }

    public function notifyCompleted(User $user, AiContent $content): void
    {
        $this->notifications->notifyUser($user, [
            'category' => 'ai_studio',
            'sound' => 'default',
            'priority' => 'normal',
            'icon' => '✨',
            'title' => __('ai_studio.generation_complete_title'),
            'body' => __('ai_studio.generation_complete_body', ['type' => __('ai_studio.types.'.$content->type->value)]),
            'url' => '/ai-studio',
        ]);
    }

    private function queueMedia(User $user, string $prompt, AiContentType $type, string $jobClass, ?UploadedFile $reference = null): AiContent
    {
        if (! $this->credits->canAfford($user, $type)) {
            throw new RuntimeException(__('ai_studio.insufficient_credits'));
        }

        $enhanced = $this->prompts->enhance($prompt, $type);
        $metadata = ['enhanced_prompt' => $enhanced];

        $content = AiContent::create([
            'user_id' => $user->id,
            'type' => $type,
            'prompt' => $prompt,
            'status' => AiContentStatus::Processing,
            'metadata' => $metadata,
        ]);

        if ($reference) {
            $content->update([
                'metadata' => array_merge($content->metadata ?? [], $this->references->store($content, $reference)),
            ]);
        }

        $this->credits->deduct($user, $type);
        $jobClass::dispatch($content->id);

        return $content->fresh();
    }

    private function storeText(string $userId, string $contentId, string $text): string
    {
        $path = "ai-studio/{$userId}/text/{$contentId}.txt";
        Storage::disk('public')->put($path, $text);

        return $path;
    }

    private function notifyFailed(User $user, AiContent $content, string $error): void
    {
        $this->notifications->notifyUser($user, [
            'category' => 'ai_studio',
            'sound' => 'alert',
            'priority' => 'urgent',
            'icon' => '⚠️',
            'title' => __('ai_studio.generation_failed_title'),
            'body' => __('ai_studio.generation_failed_body', ['type' => __('ai_studio.types.'.$content->type->value)]),
            'url' => '/ai-studio',
        ]);
    }

    private function pollLumaGeneration(string $apiKey, string $generationId, int $maxAttempts = 60): string
    {
        $url = config('ai_studio.luma_api_url').'/'.$generationId;

        for ($i = 0; $i < $maxAttempts; $i++) {
            sleep(5);

            $response = \Illuminate\Support\Facades\Http::withToken($apiKey)->get($url);

            if (! $response->successful()) {
                continue;
            }

            $state = $response->json('state');
            $assets = $response->json('assets');

            if ($state === 'completed' && ! empty($assets['video'])) {
                return $assets['video'];
            }

            if ($state === 'failed') {
                throw new RuntimeException('Luma video generation failed.');
            }
        }

        throw new RuntimeException('Luma video generation timed out.');
    }
}
