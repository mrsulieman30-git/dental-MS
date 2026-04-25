<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Waitlist extends Model
{
    protected $table = 'waitlist';

    protected $fillable = [
        'tenant_id', 'patient_id', 'location_id', 'provider_id',
        'appointment_type_id', 'preferred_days', 'preferred_time_start',
        'preferred_time_end', 'flexibility_level', 'notes', 'status',
        'added_at', 'contacted_at', 'scheduled_appointment_id'
    ];

    protected $casts = [
        'preferred_days' => 'array',
        'added_at' => 'datetime',
        'contacted_at' => 'datetime',
    ];

    public function tenant(): BelongsTo { return $this->belongsTo(Tenant::class); }
    public function patient(): BelongsTo { return $this->belongsTo(Patient::class); }
    public function location(): BelongsTo { return $this->belongsTo(Location::class); }
    public function provider(): BelongsTo { return $this->belongsTo(User::class, 'provider_id'); }
    public function appointmentType(): BelongsTo { return $this->belongsTo(AppointmentType::class); }
    public function appointment(): BelongsTo { return $this->belongsTo(Appointment::class, 'scheduled_appointment_id'); }
}
