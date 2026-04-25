<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TreatmentPlanProcedure extends Model
{
    protected $fillable = [
        'treatment_plan_id', 'phase', 'sequence_order', 'tooth_number',
        'surfaces', 'cdt_code_id', 'procedure_name', 'fee',
        'insurance_estimated', 'patient_portion', 'status', 'priority',
        'appointment_id', 'notes', 'declined_at', 'declined_reason'
    ];

    protected $casts = [
        'surfaces' => 'array',
        'fee' => 'decimal:2',
        'insurance_estimated' => 'decimal:2',
        'patient_portion' => 'decimal:2',
        'declined_at' => 'datetime',
    ];

    public function treatmentPlan(): BelongsTo { return $this->belongsTo(TreatmentPlan::class); }
    public function cdtCode(): BelongsTo { return $this->belongsTo(CdtCode::class, 'cdt_code_id'); }
    public function appointment(): BelongsTo { return $this->belongsTo(Appointment::class); }
}
