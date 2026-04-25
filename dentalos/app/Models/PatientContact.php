<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PatientContact extends Model
{
    protected $fillable = [
        'patient_id', 'type', 'phone_number', 'label', 'is_primary',
        'is_sms_enabled', 'contact_name', 'relationship'
    ];

    protected $casts = [
        'is_primary' => 'boolean',
        'is_sms_enabled' => 'boolean',
    ];

    public function patient(): BelongsTo { return $this->belongsTo(Patient::class); }
}
