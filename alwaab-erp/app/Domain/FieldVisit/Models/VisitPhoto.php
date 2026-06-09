<?php

namespace App\Domain\FieldVisit\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VisitPhoto extends Model
{
    use HasUuid;

    protected $fillable = [
        'visit_id', 'original_url', 'watermarked_url',
        'lat', 'lng', 'taken_at', 'watermark_text',
    ];

    protected function casts(): array
    {
        return [
            'lat' => 'decimal:7',
            'lng' => 'decimal:7',
            'taken_at' => 'datetime',
            'watermark_text' => 'array',
        ];
    }

    public function visit(): BelongsTo
    {
        return $this->belongsTo(FieldVisit::class, 'visit_id');
    }
}
