<?php

namespace App\Models;

use App\Enums\Locale;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, HasRoles, HasUuid, LogsActivity, Notifiable, SoftDeletes;

    /** Spatie permissions are registered on the web guard (Sanctum API tokens too). */
    protected $guard_name = 'web';

    protected $fillable = [
        'name_ar',
        'name_en',
        'email',
        'phone',
        'password',
        'locale',
        'is_active',
        'ai_credits',
        'last_login_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'ai_credits' => 'integer',
            'locale' => Locale::class,
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name_ar', 'name_en', 'email', 'phone', 'is_active', 'locale'])
            ->logOnlyDirty();
    }

    public function getNameAttribute(): string
    {
        return app()->getLocale() === 'ar' ? $this->name_ar : $this->name_en;
    }

    public function employeeProfile(): HasOne
    {
        return $this->hasOne(\App\Domain\HR\Models\EmployeeProfile::class);
    }

    public function assignedContacts(): HasMany
    {
        return $this->hasMany(\App\Domain\CRM\Models\Contact::class, 'assigned_to');
    }

    public function assignedLeads(): HasMany
    {
        return $this->hasMany(\App\Domain\CRM\Models\Lead::class, 'assigned_to');
    }
}
