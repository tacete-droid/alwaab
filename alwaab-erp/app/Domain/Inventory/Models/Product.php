<?php

namespace App\Domain\Inventory\Models;

use App\Enums\ProductType;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Product extends Model
{
    use HasUuid, LogsActivity, SoftDeletes;

    protected $fillable = [
        'sku', 'source_sno', 'name_ar', 'name_en', 'category_id', 'type', 'fitting_type',
        'size', 'pressure_rating', 'unit', 'price_aed', 'price_with_markup_aed',
        'datasheet_url', 'certifications', 'images', 'description_ar', 'description_en', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'type' => ProductType::class,
            'price_aed' => 'decimal:2',
            'price_with_markup_aed' => 'decimal:2',
            'certifications' => 'array',
            'images' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logFillable()->logOnlyDirty();
    }

    public function getNameAttribute(): string
    {
        return app()->getLocale() === 'ar' ? $this->name_ar : $this->name_en;
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class, 'category_id');
    }

    public function inventory(): HasMany
    {
        return $this->hasMany(Inventory::class);
    }
}
