<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DentalChart extends Model
{
    protected $fillable = [
        'patient_id', 'notation_system'
    ];

    public function patient(): BelongsTo { return $this->belongsTo(Patient::class); }
    public function toothConditions(): HasMany { return $this->hasMany(ToothCondition::class); }
    public function restorations(): HasMany { return $this->hasMany(Restoration::class); }
    public function implants(): HasMany { return $this->hasMany(Implant::class); }
}
