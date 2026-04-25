<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PatientInsurance extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'patient_id', 'carrier_id', 'plan_name', 'group_number',
        'subscriber_id', 'subscriber_name', 'subscriber_dob',
        'subscriber_relationship', 'employer_name', 'effective_date',
        'termination_date', 'is_primary', 'sequence', 'annual_maximum',
        'deductible_individual', 'deductible_family', 'deductible_met',
        'benefits_used_ytd', 'benefit_year_start', 'waiting_periods',
        'covered_percentages', 'missing_tooth_clause',
        'coordination_of_benefits_type', 'pre_auth_required_above',
        'insurance_card_front_path', 'insurance_card_back_path',
        'verified_at', 'verified_by', 'eligibility_response',
        'eligibility_history'
    ];

    protected $casts = [
        'subscriber_dob' => 'date',
        'effective_date' => 'date',
        'termination_date' => 'date',
        'is_primary' => 'boolean',
        'annual_maximum' => 'decimal:2',
        'deductible_individual' => 'decimal:2',
        'deductible_family' => 'decimal:2',
        'deductible_met' => 'decimal:2',
        'benefits_used_ytd' => 'decimal:2',
        'pre_auth_required_above' => 'decimal:2',
        'waiting_periods' => 'array',
        'covered_percentages' => 'array',
        'missing_tooth_clause' => 'boolean',
        'verified_at' => 'datetime',
        'eligibility_response' => 'array',
        'eligibility_history' => 'array',
    ];

    public function patient(): BelongsTo { return $this->belongsTo(Patient::class); }
    public function carrier(): BelongsTo { return $this->belongsTo(InsuranceCarrier::class, 'carrier_id'); }
    public function verifier(): BelongsTo { return $this->belongsTo(User::class, 'verified_by'); }
    public function claims(): HasMany { return $this->hasMany(Claim::class, 'insurance_id'); }
    public function preAuths(): HasMany { return $this->hasMany(PreAuthorization::class, 'insurance_id'); }
}
