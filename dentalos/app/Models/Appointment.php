<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Appointment extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'tenant_id', 'location_id', 'patient_id', 'provider_id', 'hygienist_id',
        'operatory_id', 'appointment_type_id', 'start_time', 'end_time',
        'duration_minutes', 'status', 'is_new_patient', 'notes', 'internal_notes',
        'confirmation_status', 'confirmed_at', 'confirmed_by_method',
        'checked_in_at', 'completed_at', 'arrival_time', 'production_estimated',
        'is_recurring', 'recurring_group_id', 'parent_appointment_id',
        'cancelled_at', 'cancellation_reason', 'cancelled_by', 'no_show_reason',
        'created_by'
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'confirmed_at' => 'datetime',
        'checked_in_at' => 'datetime',
        'completed_at' => 'datetime',
        'arrival_time' => 'datetime',
        'cancelled_at' => 'datetime',
        'is_new_patient' => 'boolean',
        'is_recurring' => 'boolean',
        'production_estimated' => 'decimal:2',
    ];

    public function tenant(): BelongsTo { return $this->belongsTo(Tenant::class); }
    public function location(): BelongsTo { return $this->belongsTo(Location::class); }
    public function patient(): BelongsTo { return $this->belongsTo(Patient::class); }
    public function provider(): BelongsTo { return $this->belongsTo(User::class, 'provider_id'); }
    public function hygienist(): BelongsTo { return $this->belongsTo(User::class, 'hygienist_id'); }
    public function operatory(): BelongsTo { return $this->belongsTo(Operatory::class); }
    public function appointmentType(): BelongsTo { return $this->belongsTo(AppointmentType::class); }
    public function reminders(): HasMany { return $this->hasMany(AppointmentReminder::class); }
    public function clinicalNote(): HasOne { return $this->hasOne(ClinicalNote::class); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }

    public function scopeForTenant($query, $tenantId) { return $query->where('tenant_id', $tenantId); }
    public function scopeForLocation($query, $locationId) { return $query->where('location_id', $locationId); }
}
