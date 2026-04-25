<?php

namespace App\Models;

use DentalOS\Traits\BelongsToTenant;

class Patient extends BaseModel
{
    use BelongsToTenant;

    protected $casts = [
        'date_of_birth' => 'date',
        'is_new_patient' => 'boolean',
        'first_visit_date' => 'date',
        'last_visit_date' => 'date',
        'next_appointment_date' => 'date',
        'patient_since_date' => 'date',
        'has_portal_account' => 'boolean',
        'is_vip' => 'boolean',
        'needs_interpreter' => 'boolean',
        'has_special_needs' => 'boolean',
        'do_not_call' => 'boolean',
        'do_not_text' => 'boolean',
        'do_not_email' => 'boolean',
        'is_hipaa_signed' => 'boolean',
        'hipaa_signed_at' => 'datetime',
        'is_minor' => 'boolean',
        'portal_consent_signed_at' => 'datetime',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function primaryLocation()
    {
        return $this->belongsTo(Location::class, 'primary_location_id');
    }

    public function primaryProvider()
    {
        return $this->belongsTo(User::class, 'primary_provider_id');
    }

    public function primaryHygienist()
    {
        return $this->belongsTo(User::class, 'primary_hygienist_id');
    }

    public function responsibleParty()
    {
        return $this->belongsTo(Patient::class, 'responsible_party_id');
    }

    public function referredBy()
    {
        return $this->belongsTo(Patient::class, 'referred_by_patient_id');
    }

    public function contacts()
    {
        return $this->hasMany(PatientContact::class);
    }

    public function addresses()
    {
        return $this->hasMany(PatientAddress::class);
    }

    public function emails()
    {
        return $this->hasMany(PatientEmail::class);
    }

    public function alerts()
    {
        return $this->hasMany(PatientAlert::class);
    }
}
