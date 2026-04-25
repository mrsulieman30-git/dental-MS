<?php

namespace App\Models;

class PatientAlert extends BaseModel
{
    protected $casts = [
        'is_active' => 'boolean',
        'show_at_checkin' => 'boolean',
        'expires_at' => 'datetime',
    ];

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
