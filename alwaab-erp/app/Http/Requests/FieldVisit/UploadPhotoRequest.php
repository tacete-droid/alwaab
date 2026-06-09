<?php

namespace App\Http\Requests\FieldVisit;

use Illuminate\Foundation\Http\FormRequest;

class UploadPhotoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('visits.create') ?? false;
    }

    public function rules(): array
    {
        return [
            'photo' => ['required', 'image', 'max:10240'],
            'lat' => ['nullable', 'numeric', 'between:-90,90'],
            'lng' => ['nullable', 'numeric', 'between:-180,180'],
        ];
    }
}
