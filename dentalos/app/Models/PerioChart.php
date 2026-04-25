<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PerioChart extends Model
{
    protected $fillable = [
        'patient_id', 'appointment_id', 'provider_id', 'chart_date',
        'aap_stage', 'aap_grade', 'overall_risk_level', 'notes'
    ];

    protected $casts = [
        'chart_date' => 'date',
    ];

    public function patient(): BelongsTo { return $this->belongsTo(Patient::class); }
    public function appointment(): BelongsTo { return $this->belongsTo(Appointment::class); }
    public function provider(): BelongsTo { return $this->belongsTo(User::class); }
    public function measurements(): HasMany { return $this->hasMany(PerioMeasurement::class); }
}
