<?php

namespace App\Domain\Inventory\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Inventory extends Model
{
    use HasUuid;

    protected $table = 'inventory';

    protected $fillable = [
        'product_id', 'warehouse_id', 'qty_on_hand',
        'qty_reserved', 'reorder_point', 'unit',
    ];

    protected function casts(): array
    {
        return [
            'qty_on_hand' => 'decimal:3',
            'qty_reserved' => 'decimal:3',
            'reorder_point' => 'decimal:3',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function getQtyAvailableAttribute(): float
    {
        return (float) $this->qty_on_hand - (float) $this->qty_reserved;
    }

    public function isLowStock(): bool
    {
        return $this->qty_on_hand <= $this->reorder_point;
    }
}
