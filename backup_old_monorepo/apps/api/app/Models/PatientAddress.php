<?php

namespace App\Models;

class PatientAddress extends BaseModel
{
    protected $casts = [
        'is_primary' => 'boolean',
    ];

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }
}
