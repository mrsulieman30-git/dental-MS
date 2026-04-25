<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TreatmentPlan extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'patient_id', 'location_id', 'created_by', 'name', 'status',
        'presented_at', 'accepted_at', 'declined_at', 'total_fee',
        'insurance_estimated', 'patient_estimated', 'notes',
        'expires_at', 'version', 'parent_plan_id', 'signed_by_patient',
        'patient_signed_at', 'signature_image_path', 'phase_names',
        'alternative_group', 'signer_ip', 'signer_name'
    ];

    protected $casts = [
        'presented_at' => 'datetime',
        'accepted_at' => 'datetime',
        'declined_at' => 'datetime',
        'expires_at' => 'date',
        'patient_signed_at' => 'datetime',
        'signed_by_patient' => 'boolean',
        'total_fee' => 'decimal:2',
        'insurance_estimated' => 'decimal:2',
        'patient_estimated' => 'decimal:2',
        'phase_names' => 'array',
    ];

    protected $appends = ['phases_count', 'procedures_count'];

    public function getPhasesCountAttribute(): int
    {
        return $this->procedures()->distinct('phase')->count('phase');
    }

    public function getProceduresCountAttribute(): int
    {
        return $this->procedures()->count();
    }

    public function patient(): BelongsTo { return $this->belongsTo(Patient::class); }
    public function location(): BelongsTo { return $this->belongsTo(Location::class); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
    public function procedures(): HasMany { return $this->hasMany(TreatmentPlanProcedure::class); }
    public function parentPlan(): BelongsTo { return $this->belongsTo(TreatmentPlan::class, 'parent_plan_id'); }
    public function alternativePlans()
    {
        return $this->hasMany(TreatmentPlan::class, 'alternative_group', 'alternative_group')
            ->where('id', '!=', $this->id);
    }
}
