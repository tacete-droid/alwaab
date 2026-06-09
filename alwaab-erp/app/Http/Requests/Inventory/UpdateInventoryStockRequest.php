<?php

namespace App\Http\Requests\Inventory;

use Illuminate\Foundation\Http\FormRequest;

class UpdateInventoryStockRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('inventory.manage') ?? false;
    }

    public function rules(): array
    {
        return [
            'qty_on_hand' => ['required', 'numeric', 'min:0', 'max:9999999.999'],
            'reorder_point' => ['required', 'numeric', 'min:0', 'max:9999999.999'],
        ];
    }
}
