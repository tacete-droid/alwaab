<?php

namespace App\Domain\Quotations\Models;

use App\Domain\Inventory\Models\Product;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RfqBoqItem extends Model
{
    use HasUuid;

    protected $fillable = [
        'rfq_id', 'row_number', 'description', 'client_enquiry',
        'quantity', 'unit', 'unit_price_aed', 'total_aed',
        'product_id', 'category', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'unit_price_aed' => 'decimal:2',
            'total_aed' => 'decimal:2',
        ];
    }

    public function rfq(): BelongsTo
    {
        return $this->belongsTo(Rfq::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
