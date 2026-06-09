<?php

namespace App\Http\Requests\Inventory;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProductPriceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('products.update') ?? false;
    }

    public function rules(): array
    {
        return [
            'price_aed' => ['required', 'numeric', 'min:0', 'max:999999.99'],
            'price_with_markup_aed' => ['nullable', 'numeric', 'min:0', 'max:999999.99'],
        ];
    }
}
