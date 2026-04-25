<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InsuranceCarrier extends Model
{
    protected $fillable = [
        'tenant_id', 'name', 'address', 'phone', 'fax', 'email', 'website',
        'payer_id', 'is_active', 'notes', 'logo_path'
    ];

    protected $casts = [
        'address' => 'array',
        'is_active' => 'boolean',
    ];

    public function tenant(): BelongsTo { return $this->belongsTo(Tenant::class); }
    public function patientInsurances(): HasMany { return $this->hasMany(PatientInsurance::class, 'carrier_id'); }
    public function feeSchedules(): HasMany { return $this->hasMany(FeeSchedule::class, 'carrier_id'); }
}
