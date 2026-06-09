<?php

namespace App\Http\Requests\Invoices;

use App\Enums\InvoiceType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreInvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('invoices.create') ?? false;
    }

    public function rules(): array
    {
        return [
            'type' => ['required', Rule::enum(InvoiceType::class)],
            'document_date' => ['required', 'date'],
            'contact_id' => ['nullable', 'uuid', 'exists:contacts,id'],
            'project_id' => ['nullable', 'uuid', 'exists:projects,id'],
            'client_name' => ['nullable', 'string', 'max:255'],
            'attention_to' => ['nullable', 'string', 'max:255'],
            'project_name' => ['nullable', 'string', 'max:255'],
            'consultant' => ['nullable', 'string', 'max:255'],
            'main_contractor' => ['nullable', 'string', 'max:255'],
            'mep_contractor' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'lpo_number' => ['nullable', 'string', 'max:100'],
            'address' => ['nullable', 'string', 'max:1000'],
            'discount_aed' => ['nullable', 'numeric', 'min:0'],
            'valid_until' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:5000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['nullable', 'uuid', 'exists:products,id'],
            'items.*.description' => ['nullable', 'string', 'max:500'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit' => ['nullable', 'string', 'max:20'],
            'items.*.unit_price_aed' => ['required', 'numeric', 'min:0'],
        ];
    }
}
