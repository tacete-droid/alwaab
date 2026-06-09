<?php

namespace App\Domain\CRM\Models;

use App\Domain\CRM\Models\Lead;
use App\Enums\ContactStatus;
use App\Enums\ContactType;
use App\Models\User;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Contact extends Model
{
    use HasUuid, LogsActivity, SoftDeletes;

    protected $fillable = [
        'type', 'name_ar', 'name_en', 'company', 'email', 'phone',
        'emirate', 'status', 'tags', 'notes', 'assigned_to',
    ];

    protected function casts(): array
    {
        return [
            'type' => ContactType::class,
            'status' => ContactStatus::class,
            'tags' => 'array',
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

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function leads(): HasMany
    {
        return $this->hasMany(Lead::class);
    }

    public function activities(): MorphMany
    {
        return $this->morphMany(Activity::class, 'subject');
    }
}
