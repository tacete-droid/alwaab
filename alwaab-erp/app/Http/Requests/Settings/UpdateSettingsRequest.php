<?php

namespace App\Http\Requests\Settings;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('settings.manage') ?? false;
    }

    public function rules(): array
    {
        return [
            'company_name_ar' => ['required', 'string', 'max:255'],
            'company_name_en' => ['required', 'string', 'max:255'],
            'company_phone' => ['nullable', 'string', 'max:50'],
            'company_email' => ['nullable', 'email', 'max:255'],
            'company_address' => ['nullable', 'string', 'max:500'],
            'company_trn' => ['nullable', 'string', 'max:50'],
            'quotation_validity_days' => ['required', 'integer', 'min:1', 'max:365'],
            'low_stock_alert' => ['boolean'],
        ];
    }
}
