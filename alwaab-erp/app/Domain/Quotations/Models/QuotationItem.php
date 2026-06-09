<?php

namespace App\Domain\Quotations\Models;

use App\Domain\Inventory\Models\Product;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuotationItem extends Model
{
    use HasUuid;

    protected $fillable = [
        'quotation_id', 'product_id', 'description', 'quantity',
        'unit', 'unit_price_aed', 'total_aed', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:3',
            'unit_price_aed' => 'decimal:2',
            'total_aed' => 'decimal:2',
        ];
    }

    public function quotation(): BelongsTo
    {
        return $this->belongsTo(Quotation::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
