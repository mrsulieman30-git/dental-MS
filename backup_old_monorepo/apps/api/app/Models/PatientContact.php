<?php

namespace App\Models;

class PatientContact extends BaseModel
{
    protected $casts = [
        'is_primary' => 'boolean',
        'is_sms_enabled' => 'boolean',
    ];

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }
}
