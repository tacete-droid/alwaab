<?php

namespace App\Services;

use App\Enums\AiContentType;

class AiPromptService
{
    public function enhance(string $prompt, AiContentType $type): string
    {
        $prompt = trim($prompt);

        return match ($type) {
            AiContentType::Image => $prompt.', '.config('ai_studio.image_prompt_suffix'),
            AiContentType::Video => $prompt.', '.config('ai_studio.video_prompt_suffix'),
            AiContentType::Text => $prompt,
        };
    }
}
