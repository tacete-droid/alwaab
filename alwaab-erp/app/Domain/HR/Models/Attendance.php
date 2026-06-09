<?php

namespace App\Domain\HR\Models;

use App\Enums\AttendanceStatus;
use App\Models\User;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendance extends Model
{
    use HasUuid;

    protected $fillable = [
        'user_id', 'check_in_at', 'check_out_at',
        'check_in_lat', 'check_in_lng', 'check_out_lat', 'check_out_lng',
        'status', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'status' => AttendanceStatus::class,
            'check_in_at' => 'datetime',
            'check_out_at' => 'datetime',
            'check_in_lat' => 'decimal:7',
            'check_in_lng' => 'decimal:7',
            'check_out_lat' => 'decimal:7',
            'check_out_lng' => 'decimal:7',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getDurationHoursAttribute(): ?float
    {
        if (! $this->check_out_at) {
            return null;
        }

        return round($this->check_in_at->diffInMinutes($this->check_out_at) / 60, 2);
    }
}
