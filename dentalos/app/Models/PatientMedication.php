<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PatientMedication extends Model
{
    protected $fillable = [
        'patient_id', 'drug_name', 'generic_name', 'dosage', 'unit',
        'frequency', 'route', 'prescribing_doctor', 'prescribing_doctor_phone',
        'start_date', 'end_date', 'is_active', 'reason', 'notes'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean',
    ];

    public function patient(): BelongsTo { return $this->belongsTo(Patient::class); }
}
