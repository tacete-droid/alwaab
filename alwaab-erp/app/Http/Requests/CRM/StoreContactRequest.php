<?php

namespace App\Http\Requests\CRM;

use App\Enums\ContactStatus;
use App\Enums\ContactType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreContactRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('contacts.create') ?? false;
    }

    public function rules(): array
    {
        return [
            'type' => ['required', Rule::enum(ContactType::class)],
            'name_ar' => ['required', 'string', 'max:255'],
            'name_en' => ['required', 'string', 'max:255'],
            'company' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'emirate' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', Rule::enum(ContactStatus::class)],
            'tags' => ['nullable', 'array'],
            'notes' => ['nullable', 'string'],
            'assigned_to' => ['nullable', 'uuid', 'exists:users,id'],
        ];
    }
}
