<?php

namespace App\Domain\FieldVisit\Models;

use App\Domain\Projects\Models\Project;
use App\Enums\FieldVisitStatus;
use App\Models\User;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FieldVisit extends Model
{
    use HasUuid;

    protected $fillable = [
        'project_id', 'employee_id', 'lat', 'lng',
        'visited_at', 'completed_at', 'notes', 'status',
    ];

    protected function casts(): array
    {
        return [
            'status' => FieldVisitStatus::class,
            'lat' => 'decimal:7',
            'lng' => 'decimal:7',
            'visited_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'employee_id');
    }

    public function photos(): HasMany
    {
        return $this->hasMany(VisitPhoto::class, 'visit_id');
    }
}
