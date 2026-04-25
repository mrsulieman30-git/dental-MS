<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Carbon\Carbon;

class Patient extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'tenant_id', 'patient_number', 'first_name', 'middle_name', 'last_name',
        'preferred_name', 'date_of_birth', 'gender', 'pronouns', 'ssn_last4',
        'photo_path', 'status', 'primary_location_id', 'primary_provider_id',
        'primary_hygienist_id', 'responsible_party_id', 'preferred_language',
        'preferred_communication', 'is_new_patient', 'first_visit_date',
        'last_visit_date', 'next_appointment_date', 'patient_since_date',
        'source', 'referred_by_patient_id', 'referred_by_source',
        'is_vip', 'needs_interpreter', 'has_special_needs', 'special_needs_notes',
        'internal_notes', 'billing_notes', 'do_not_call', 'do_not_text',
        'do_not_email', 'is_hipaa_signed', 'hipaa_signed_at', 'is_minor',
        'guardian_name', 'guardian_relationship', 'guardian_phone',
        'stripe_customer_id', 'stripe_payment_method_id', 'last_statement_at'
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'first_visit_date' => 'date',
        'last_visit_date' => 'date',
        'next_appointment_date' => 'datetime',
        'patient_since_date' => 'date',
        'hipaa_signed_at' => 'datetime',
        'is_new_patient' => 'boolean',
        'has_portal_account' => 'boolean',
        'is_vip' => 'boolean',
        'needs_interpreter' => 'boolean',
        'has_special_needs' => 'boolean',
        'do_not_call' => 'boolean',
        'do_not_text' => 'boolean',
        'do_not_email' => 'boolean',
        'is_hipaa_signed' => 'boolean',
        'is_minor' => 'boolean',
        'last_statement_at' => 'datetime',
    ];

    protected $appends = ['full_name', 'age', 'balance'];

    protected static function booted()
    {
        static::creating(function ($patient) {
            if (empty($patient->patient_number)) {
                $lastPatient = static::where('tenant_id', $patient->tenant_id)
                    ->orderBy('id', 'desc')
                    ->first();
                $nextNumber = $lastPatient ? (int) str_replace('P-', '', $lastPatient->patient_number) + 1 : 1;
                $patient->patient_number = 'P-' . str_pad($nextNumber, 5, '0', STR_PAD_LEFT);
            }
        });
    }

    public function tenant(): BelongsTo { return $this->belongsTo(Tenant::class); }
    public function contacts(): HasMany { return $this->hasMany(PatientContact::class); }
    public function addresses(): HasMany { return $this->hasMany(PatientAddress::class); }
    public function emails(): HasMany { return $this->hasMany(PatientEmail::class); }
    public function alerts(): HasMany { return $this->hasMany(PatientAlert::class); }
    public function appointments(): HasMany { return $this->hasMany(Appointment::class); }
    public function chart(): HasOne { return $this->hasOne(DentalChart::class); }
    public function medicalHistories(): HasMany { return $this->hasMany(MedicalHistory::class); }
    public function insurance(): HasMany { return $this->hasMany(PatientInsurance::class); }
    public function primaryInsurance() { return $this->insurance()->where('is_primary', true)->first(); }
    public function ledgerEntries(): HasMany { return $this->hasMany(LedgerEntry::class); }
    public function payments(): HasMany { return $this->hasMany(Payment::class); }
    public function claims(): HasMany { return $this->hasMany(Claim::class); }
    public function paymentPlans(): HasMany { return $this->hasMany(PaymentPlan::class); }
    public function statements(): HasMany { return $this->hasMany(Statement::class); }
    public function clinicalNotes(): HasMany { return $this->hasMany(ClinicalNote::class); }
    public function perioCharts(): HasMany { return $this->hasMany(PerioChart::class); }
    public function imagingSeries(): HasMany { return $this->hasMany(ImagingSeries::class); }
    public function dentalImages(): HasMany { return $this->hasMany(DentalImage::class); }

    public function getFullNameAttribute(): string { return "{$this->first_name} {$this->last_name}"; }
    public function getAgeAttribute(): int { return Carbon::parse($this->date_of_birth)->age; }
    public function getBalanceAttribute(): float { return (float) $this->ledgerEntries()->where('is_void', false)->sum('amount'); }

    public function scopeActive($query) { return $query->where('status', 'active'); }
    public function scopeForTenant($query, $tenantId) { return $query->where('tenant_id', $tenantId); }
}
