<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PreAuthorization extends Model
{
    protected $fillable = [
        'tenant_id', 'patient_id', 'insurance_id', 'cdt_code_id',
        'procedure_description', 'tooth_number', 'status', 'auth_number',
        'submitted_at', 'response_at', 'expiry_date', 'notes', 'created_by'
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'response_at' => 'datetime',
        'expiry_date' => 'date',
    ];

    public function patient(): BelongsTo { return $this->belongsTo(Patient::class); }
    public function insurance(): BelongsTo { return $this->belongsTo(PatientInsurance::class, 'insurance_id'); }
    public function cdtCode(): BelongsTo { return $this->belongsTo(CdtCode::class, 'cdt_code_id'); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
    public function tenant(): BelongsTo { return $this->belongsTo(Tenant::class); }
}
