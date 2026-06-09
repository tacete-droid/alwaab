<?php

namespace App\Http\Requests\Quotations;

use Illuminate\Foundation\Http\FormRequest;

class UploadBoqRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('quotations.create') ?? false;
    }

    public function rules(): array
    {
        return [
            'boq_file' => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:10240'],
        ];
    }

    public function attributes(): array
    {
        return [
            'boq_file' => __('quotations.boq_file'),
        ];
    }
}
