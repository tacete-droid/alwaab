<?php

namespace App\Http\Requests\Quotations;

use Illuminate\Foundation\Http\FormRequest;

class StoreRfqRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('quotations.create') ?? false;
    }

    public function rules(): array
    {
        return [
            'contact_id' => ['required', 'uuid', 'exists:contacts,id'],
            'project_id' => ['nullable', 'uuid', 'exists:projects,id'],
            'description' => ['nullable', 'string', 'max:2000'],
            'assigned_to' => ['nullable', 'uuid', 'exists:users,id'],
        ];
    }
}
