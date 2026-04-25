<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Prescription extends Model
{
    protected $fillable = [
        'patient_id', 'prescriber_id', 'drug_name', 'generic_name',
        'ndc_code', 'dosage', 'dosage_unit', 'quantity', 'refills',
        'frequency', 'route', 'sig', 'is_controlled_substance',
        'dea_schedule', 'diagnosis_code', 'pharmacy_name',
        'pharmacy_ncpdp_id', 'pharmacy_address', 'status', 'sent_at',
        'dispensed_at', 'external_rx_id', 'notes'
    ];

    protected $casts = [
        'pharmacy_address' => 'array',
        'is_controlled_substance' => 'boolean',
        'sent_at' => 'datetime',
        'dispensed_at' => 'datetime',
    ];

    public function patient(): BelongsTo { return $this->belongsTo(Patient::class); }
    public function prescriber(): BelongsTo { return $this->belongsTo(User::class, 'prescriber_id'); }
}
