<?php

namespace App\Http\Requests\Inventory;

use App\Enums\StockMovementType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreStockMovementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('inventory.move') ?? false;
    }

    public function rules(): array
    {
        return [
            'product_id' => ['required', 'uuid', 'exists:products,id'],
            'warehouse_id' => ['required', 'uuid', 'exists:warehouses,id'],
            'type' => ['required', Rule::enum(StockMovementType::class)],
            'quantity' => ['required', 'numeric', 'min:0.001'],
            'to_warehouse_id' => [
                Rule::requiredIf($this->input('type') === StockMovementType::Transfer->value),
                'nullable', 'uuid', 'exists:warehouses,id', 'different:warehouse_id',
            ],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
