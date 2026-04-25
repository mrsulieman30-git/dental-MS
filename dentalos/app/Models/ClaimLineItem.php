<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClaimLineItem extends Model
{
    protected $fillable = [
        'claim_id', 'treatment_plan_procedure_id', 'tooth_number',
        'surfaces', 'cdt_code_id', 'description', 'fee_billed',
        'fee_allowed', 'insurance_paid', 'patient_portion',
        'adjustment', 'adjustment_type', 'status', 'denial_reason'
    ];

    protected $casts = [
        'surfaces' => 'array',
        'fee_billed' => 'decimal:2',
        'fee_allowed' => 'decimal:2',
        'insurance_paid' => 'decimal:2',
        'patient_portion' => 'decimal:2',
        'adjustment' => 'decimal:2',
    ];

    public function claim(): BelongsTo { return $this->belongsTo(Claim::class); }
    public function cdtCode(): BelongsTo { return $this->belongsTo(CdtCode::class); }
}
