<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LabCase extends Model
{
    protected $fillable = [
        'tenant_id', 'case_number', 'patient_id', 'location_id', 'lab_id',
        'provider_id', 'appointment_id', 'try_in_appointment_id',
        'seat_appointment_id', 'procedure_description', 'cdt_code_id',
        'tooth_numbers', 'shade', 'material', 'specific_instructions',
        'prescription_path', 'photos', 'status', 'sent_date', 'due_date',
        'received_date', 'approved_date', 'lab_invoice_number',
        'lab_cost', 'is_redo', 'redo_reason', 'created_by'
    ];

    protected $casts = [
        'tooth_numbers' => 'array',
        'photos' => 'array',
        'sent_date' => 'date',
        'due_date' => 'date',
        'received_date' => 'date',
        'approved_date' => 'date',
        'lab_cost' => 'decimal:2',
        'is_redo' => 'boolean',
    ];

    public function tenant(): BelongsTo { return $this->belongsTo(Tenant::class); }
    public function patient(): BelongsTo { return $this->belongsTo(Patient::class); }
    public function location(): BelongsTo { return $this->belongsTo(Location::class); }
    public function lab(): BelongsTo { return $this->belongsTo(Lab::class); }
    public function provider(): BelongsTo { return $this->belongsTo(User::class, 'provider_id'); }
    public function appointment(): BelongsTo { return $this->belongsTo(Appointment::class); }
    public function tryInAppointment(): BelongsTo { return $this->belongsTo(Appointment::class, 'try_in_appointment_id'); }
    public function seatAppointment(): BelongsTo { return $this->belongsTo(Appointment::class, 'seat_appointment_id'); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
}
