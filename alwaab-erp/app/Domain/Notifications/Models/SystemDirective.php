<?php

namespace App\Domain\Notifications\Models;

use App\Models\User;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SystemDirective extends Model
{
    use HasUuid;

    protected $fillable = [
        'title_ar', 'title_en', 'body_ar', 'body_en',
        'priority', 'target', 'target_role', 'target_user_id', 'created_by',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function targetUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'target_user_id');
    }
}
