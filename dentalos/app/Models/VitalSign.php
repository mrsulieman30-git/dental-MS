<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VitalSign extends Model
{
    protected $fillable = [
        'patient_id', 'appointment_id', 'blood_pressure_systolic',
        'blood_pressure_diastolic', 'pulse_rate', 'respiratory_rate',
        'temperature', 'oxygen_saturation', 'weight_kg', 'height_cm',
        'bmi', 'recorded_by', 'recorded_at', 'notes'
    ];

    protected $casts = [
        'temperature' => 'decimal:1',
        'weight_kg' => 'decimal:2',
        'height_cm' => 'decimal:2',
        'bmi' => 'decimal:2',
        'recorded_at' => 'datetime',
    ];

    public function patient(): BelongsTo { return $this->belongsTo(Patient::class); }
    public function appointment(): BelongsTo { return $this->belongsTo(Appointment::class); }
    public function recorder(): BelongsTo { return $this->belongsTo(User::class, 'recorded_by'); }
}
