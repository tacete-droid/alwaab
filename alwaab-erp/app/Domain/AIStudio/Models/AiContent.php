<?php

namespace App\Domain\AIStudio\Models;

use App\Enums\AiContentStatus;
use App\Enums\AiContentType;
use App\Models\User;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiContent extends Model
{
    use HasUuid;

    protected $fillable = [
        'user_id', 'type', 'prompt', 'file_path', 'status', 'metadata',
    ];

    protected function casts(): array
    {
        return [
            'type' => AiContentType::class,
            'status' => AiContentStatus::class,
            'metadata' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
