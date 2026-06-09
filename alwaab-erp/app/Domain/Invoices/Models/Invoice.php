<?php

namespace App\Domain\Invoices\Models;

use App\Domain\CRM\Models\Contact;
use App\Domain\Projects\Models\Project;
use App\Domain\Quotations\Models\Quotation;
use App\Domain\Quotations\Models\Rfq;
use App\Enums\InvoiceStatus;
use App\Enums\InvoiceType;
use App\Models\User;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Invoice extends Model
{
    use HasUuid, LogsActivity, SoftDeletes;

    protected $fillable = [
        'number', 'type', 'status', 'source', 'document_date',
        'contact_id', 'project_id', 'quotation_id', 'rfq_id',
        'client_name', 'attention_to', 'project_name',
        'consultant', 'main_contractor', 'mep_contractor',
        'phone', 'email', 'lpo_number', 'address',
        'subtotal_aed', 'discount_aed', 'vat_aed', 'total_aed',
        'valid_until', 'notes', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'type' => InvoiceType::class,
            'status' => InvoiceStatus::class,
            'document_date' => 'date',
            'valid_until' => 'date',
            'subtotal_aed' => 'decimal:2',
            'discount_aed' => 'decimal:2',
            'vat_aed' => 'decimal:2',
            'total_aed' => 'decimal:2',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logFillable()->logOnlyDirty();
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function quotation(): BelongsTo
    {
        return $this->belongsTo(Quotation::class);
    }

    public function rfq(): BelongsTo
    {
        return $this->belongsTo(Rfq::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class)->orderBy('sort_order');
    }
}
