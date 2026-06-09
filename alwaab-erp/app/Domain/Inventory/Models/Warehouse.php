<?php

namespace App\Domain\Inventory\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Warehouse extends Model
{
    use HasUuid;

    protected $fillable = [
        'name_ar', 'name_en', 'location', 'lat', 'lng', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'lat' => 'decimal:7',
            'lng' => 'decimal:7',
            'is_active' => 'boolean',
        ];
    }

    public function getNameAttribute(): string
    {
        return app()->getLocale() === 'ar' ? $this->name_ar : $this->name_en;
    }

    public function inventory(): HasMany
    {
        return $this->hasMany(Inventory::class);
    }
}
