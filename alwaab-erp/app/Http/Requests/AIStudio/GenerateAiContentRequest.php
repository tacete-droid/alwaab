<?php

namespace App\Http\Requests\AIStudio;

use Illuminate\Foundation\Http\FormRequest;

class GenerateAiContentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('access-ai-studio') ?? false;
    }

    public function rules(): array
    {
        return [
            'prompt' => ['required', 'string', 'min:3', 'max:4000'],
            'reference_file' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,gif,mp4,mov,webm', 'max:51200'],
        ];
    }
}
