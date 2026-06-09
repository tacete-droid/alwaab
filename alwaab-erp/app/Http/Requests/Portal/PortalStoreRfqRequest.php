<?php

namespace App\Http\Requests\Portal;

use Illuminate\Foundation\Http\FormRequest;

class PortalStoreRfqRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('customer') ?? false;
    }

    public function rules(): array
    {
        return [
            'project_id' => ['nullable', 'uuid', 'exists:projects,id'],
            'description' => ['required', 'string', 'max:2000'],
        ];
    }
}
