<?php

namespace App\Domain\HR\Models;

use App\Models\User;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class EmployeeProfile extends Model implements HasMedia
{
    use HasUuid;
    use InteractsWithMedia;

    protected $fillable = [
        'user_id', 'employee_code', 'job_title', 'department',
        'hire_date', 'salary_aed', 'emergency_contact',
        'emirates_id', 'emirates_id_expiry',
        'passport_number', 'passport_expiry',
        'residency_number', 'residency_expiry',
        'basic_salary', 'housing_allowance', 'iban',
    ];

    protected function casts(): array
    {
        return [
            'hire_date' => 'date',
            'salary_aed' => 'decimal:2',
            'basic_salary' => 'decimal:2',
            'housing_allowance' => 'decimal:2',
            'emirates_id_expiry' => 'date',
            'passport_expiry' => 'date',
            'residency_expiry' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('hr_documents')
            ->acceptsMimeTypes([
                'application/pdf',
                'image/jpeg',
                'image/png',
                'image/webp',
            ]);
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->width(200)
            ->height(200)
            ->nonQueued();
    }

    public function totalCompensation(): float
    {
        return (float) ($this->basic_salary ?? $this->salary_aed ?? 0)
            + (float) ($this->housing_allowance ?? 0);
    }
}
