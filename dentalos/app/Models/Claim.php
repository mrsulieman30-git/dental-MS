<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Claim extends Model
{
    protected $fillable = [
        'tenant_id', 'claim_number', 'patient_id', 'insurance_id',
        'appointment_id', 'rendering_provider_id', 'billing_provider_id',
        'location_id', 'claim_type', 'status', 'is_scrubbed', 'scrubbing_errors',
        'total_billed', 'total_paid', 'patient_portion', 'write_off',
        'submitted_at', 'paid_at', 'check_number', 'check_date', 'era_id',
        'secondary_claim_id', 'claim_form_path', 'clearinghouse_claim_id',
        'rejection_codes', 'denial_reason', 'appeal_notes', 'pre_auth_number'
    ];

    protected $casts = [
        'total_billed' => 'decimal:2',
        'total_paid' => 'decimal:2',
        'patient_portion' => 'decimal:2',
        'write_off' => 'decimal:2',
        'submitted_at' => 'datetime',
        'paid_at' => 'datetime',
        'check_date' => 'date',
        'is_scrubbed' => 'boolean',
        'scrubbing_errors' => 'array',
        'rejection_codes' => 'array',
    ];

    public function secondaryClaim(): BelongsTo { return $this->belongsTo(Claim::class, 'secondary_claim_id'); }


    public function tenant(): BelongsTo { return $this->belongsTo(Tenant::class); }
    public function patient(): BelongsTo { return $this->belongsTo(Patient::class); }
    public function insurance(): BelongsTo { return $this->belongsTo(PatientInsurance::class, 'insurance_id'); }
    public function appointment(): BelongsTo { return $this->belongsTo(Appointment::class); }
    public function renderingProvider(): BelongsTo { return $this->belongsTo(User::class, 'rendering_provider_id'); }
    public function billingProvider(): BelongsTo { return $this->belongsTo(User::class, 'billing_provider_id'); }
    public function location(): BelongsTo { return $this->belongsTo(Location::class); }
    public function lineItems(): HasMany { return $this->hasMany(ClaimLineItem::class); }
    public function attachments(): HasMany { return $this->hasMany(ClaimAttachment::class); }
}
