<?php

namespace App\Http\Requests\FieldVisit;

use Illuminate\Foundation\Http\FormRequest;

class UpdateVisitLocationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('visits.create') ?? false;
    }

    public function rules(): array
    {
        return [
            'lat' => ['required', 'numeric', 'between:-90,90'],
            'lng' => ['required', 'numeric', 'between:-180,180'],
        ];
    }
}
