<?php

namespace App\Http\Requests\CRM;

use App\Enums\LeadStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateLeadStageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('leads.update') ?? false;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', Rule::enum(LeadStatus::class)],
        ];
    }
}
