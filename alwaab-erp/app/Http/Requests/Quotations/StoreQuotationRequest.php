<?php

namespace App\Http\Requests\Quotations;

use Illuminate\Foundation\Http\FormRequest;

class StoreQuotationRequest extends FormRequest
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
            'rfq_id' => ['nullable', 'uuid', 'exists:rfqs,id'],
            'discount_aed' => ['nullable', 'numeric', 'min:0'],
            'valid_until' => ['nullable', 'date', 'after:today'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'uuid', 'exists:products,id'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.001'],
            'items.*.unit_price_aed' => ['nullable', 'numeric', 'min:0'],
        ];
    }
}
