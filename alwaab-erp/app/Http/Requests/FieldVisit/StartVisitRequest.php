<?php

namespace App\Http\Requests\FieldVisit;

use Illuminate\Foundation\Http\FormRequest;

class StartVisitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('visits.create') ?? false;
    }

    public function rules(): array
    {
        return [
            'project_id' => ['required', 'uuid', 'exists:projects,id'],
            'lat' => ['nullable', 'numeric', 'between:-90,90'],
            'lng' => ['nullable', 'numeric', 'between:-180,180'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
