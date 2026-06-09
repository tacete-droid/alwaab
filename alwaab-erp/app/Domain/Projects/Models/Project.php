<?php

namespace App\Domain\Projects\Models;

use App\Domain\CRM\Models\Contact;
use App\Domain\FieldVisit\Models\FieldVisit;
use App\Enums\ProjectStatus;
use App\Models\User;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Project extends Model
{
    use HasUuid, LogsActivity, SoftDeletes;

    protected $fillable = [
        'name_ar', 'name_en', 'location', 'lat', 'lng', 'status',
        'value_aed', 'start_date', 'end_date', 'description',
        'consultant_id', 'contractor_id', 'assigned_to',
    ];

    protected function casts(): array
    {
        return [
            'status' => ProjectStatus::class,
            'value_aed' => 'decimal:2',
            'lat' => 'decimal:7',
            'lng' => 'decimal:7',
            'start_date' => 'date',
            'end_date' => 'date',
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

    public function consultant(): BelongsTo
    {
        return $this->belongsTo(Contact::class, 'consultant_id');
    }

    public function contractor(): BelongsTo
    {
        return $this->belongsTo(Contact::class, 'contractor_id');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function fieldVisits(): HasMany
    {
        return $this->hasMany(FieldVisit::class);
    }
}
