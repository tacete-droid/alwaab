<?php

namespace App\Http\Requests\CRM;

use App\Enums\LeadSource;
use App\Enums\LeadStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreLeadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('leads.create') ?? false;
    }

    public function rules(): array
    {
        return [
            'contact_id' => ['required', 'uuid', 'exists:contacts,id'],
            'source' => ['nullable', Rule::enum(LeadSource::class)],
            'status' => ['nullable', Rule::enum(LeadStatus::class)],
            'value_aed' => ['nullable', 'numeric', 'min:0'],
            'probability' => ['nullable', 'integer', 'min:0', 'max:100'],
            'expected_close' => ['nullable', 'date'],
            'assigned_to' => ['nullable', 'uuid', 'exists:users,id'],
        ];
    }
}
