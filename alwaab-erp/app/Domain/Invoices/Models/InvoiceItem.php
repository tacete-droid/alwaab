<?php

namespace App\Domain\Invoices\Models;

use App\Domain\Inventory\Models\Product;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceItem extends Model
{
    use HasUuid;

    protected $fillable = [
        'invoice_id', 'product_id', 'description',
        'quantity', 'unit', 'unit_price_aed', 'total_aed', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'unit_price_aed' => 'decimal:2',
            'total_aed' => 'decimal:2',
        ];
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
