<?php

namespace App\Domain\Quotations\Models;

use App\Domain\CRM\Models\Contact;
use App\Domain\Projects\Models\Project;
use App\Enums\RfqStatus;
use App\Models\User;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Rfq extends Model
{
    use HasUuid, SoftDeletes;

    protected $fillable = [
        'number', 'contact_id', 'project_id', 'status', 'source',
        'description', 'website_meta', 'boq_file_url', 'assigned_to',
    ];

    protected function casts(): array
    {
        return [
            'status' => RfqStatus::class,
            'website_meta' => 'array',
        ];
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function quotations(): HasMany
    {
        return $this->hasMany(Quotation::class);
    }

    public function boqItems(): HasMany
    {
        return $this->hasMany(RfqBoqItem::class)->orderBy('sort_order');
    }
}
