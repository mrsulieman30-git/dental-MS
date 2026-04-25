<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PatientRecall extends Model
{
    protected $fillable = [
        'patient_id', 'recall_type_id', 'provider_id', 'due_date',
        'last_completed_date', 'last_completed_appointment_id',
        'status', 'appointment_id', 'notes'
    ];

    protected $casts = [
        'due_date' => 'date',
        'last_completed_date' => 'date',
    ];

    public function patient(): BelongsTo { return $this->belongsTo(Patient::class); }
    public function recallType(): BelongsTo { return $this->belongsTo(RecallType::class); }
    public function provider(): BelongsTo { return $this->belongsTo(User::class, 'provider_id'); }
    public function appointment(): BelongsTo { return $this->belongsTo(Appointment::class); }
}
