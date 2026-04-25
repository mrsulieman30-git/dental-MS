<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AppointmentType extends Model
{
    protected $fillable = [
        'tenant_id', 'name', 'color', 'default_duration_minutes',
        'is_new_patient', 'requires_pre_auth', 'default_operatory_type',
        'default_cdt_codes', 'description', 'is_active', 'sort_order'
    ];

    protected $casts = [
        'default_cdt_codes' => 'array',
        'is_new_patient' => 'boolean',
        'requires_pre_auth' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function tenant(): BelongsTo { return $this->belongsTo(Tenant::class); }
    public function appointments(): HasMany { return $this->hasMany(Appointment::class); }
}
