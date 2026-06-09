<?php

namespace App\Services;

use App\Domain\AIStudio\Models\AiContent;
use App\Enums\AiContentType;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use OpenAI\Laravel\Facades\OpenAI;
use RuntimeException;

class AiReferenceService
{
    public function store(AiContent $content, UploadedFile $file): array
    {
        $mime = $file->getMimeType() ?? '';
        $type = str_starts_with($mime, 'video/') ? 'video' : 'image';
        $ext = $file->getClientOriginalExtension() ?: ($type === 'video' ? 'mp4' : 'png');
        $path = "ai-studio/{$content->user_id}/references/{$content->id}.{$ext}";

        Storage::disk('public')->putFileAs(
            dirname($path),
            $file,
            basename($path),
        );

        return [
            'reference_type' => $type,
            'reference_path' => $path,
            'reference_url' => Storage::disk('public')->url($path),
            'reference_name' => $file->getClientOriginalName(),
            'reference_mime' => $mime,
        ];
    }

    public function applyToPrompt(AiContent $content, string $enhancedPrompt, AiContentType $type): string
    {
        $referencePath = $content->metadata['reference_path'] ?? null;
        $referenceType = $content->metadata['reference_type'] ?? null;

        if (! $referencePath || ! Storage::disk('public')->exists($referencePath)) {
            return $enhancedPrompt;
        }

        if ($referenceType === 'image') {
            $analysis = $this->analyzeImageReference($referencePath, $content->prompt, $type);

            return $enhancedPrompt."\n\nReference guidance: ".$analysis;
        }

        return $enhancedPrompt."\n\n".config('ai_studio.video_reference_suffix', 'Recreate a similar concept inspired by the uploaded reference video: matching mood, pacing, composition, and visual style while applying the user request.');
    }

    public function lumaPayloadExtras(AiContent $content): array
    {
        $referencePath = $content->metadata['reference_path'] ?? null;
        $referenceType = $content->metadata['reference_type'] ?? null;

        if ($referenceType !== 'image' || ! $referencePath) {
            return [];
        }

        if (! Storage::disk('public')->exists($referencePath)) {
            return [];
        }

        $url = Storage::disk('public')->url($referencePath);

        return [
            'keyframes' => [
                'frame0' => [
                    'type' => 'image',
                    'url' => $url,
                ],
            ],
        ];
    }

    private function analyzeImageReference(string $path, string $userPrompt, AiContentType $targetType): string
    {
        $fullPath = Storage::disk('public')->path($path);
        $mime = Storage::disk('public')->mimeType($path) ?: 'image/png';
        $base64 = base64_encode(file_get_contents($fullPath));
        $dataUri = "data:{$mime};base64,{$base64}";

        $instruction = $targetType === AiContentType::Video
            ? 'Analyze this reference image for video generation. Describe visual style, colors, branding elements, composition, and mood. Explain how to produce a similar video inspired by this reference while fulfilling the user request.'
            : 'Analyze this reference image for image generation. Describe style, colors, layout, branding, textures, and key visual elements. Explain how to create a new image inspired by this reference while fulfilling the user request.';

        try {
            $response = OpenAI::chat()->create([
                'model' => config('ai_studio.vision_model', 'gpt-4o-mini'),
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => [
                            ['type' => 'text', 'text' => "{$instruction}\n\nUser request: {$userPrompt}"],
                            ['type' => 'image_url', 'image_url' => ['url' => $dataUri]],
                        ],
                    ],
                ],
                'max_tokens' => 500,
            ]);

            return trim($response->choices[0]->message->content ?? '');
        } catch (\Throwable $e) {
            throw new RuntimeException(__('ai_studio.reference_analysis_failed').': '.$e->getMessage());
        }
    }
}
