<?php

namespace App\Http\Requests\HR;

use App\Enums\HrDocumentType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEmployeeDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('hr.view') ?? false;
    }

    public function rules(): array
    {
        return [
            'file' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:10240'],
            'document_type' => ['required', Rule::in(HrDocumentType::values())],
            'title' => ['nullable', 'string', 'max:120'],
            'expires_at' => ['nullable', 'date'],
        ];
    }
}
