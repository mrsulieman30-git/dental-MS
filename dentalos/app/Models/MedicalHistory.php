<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MedicalHistory extends Model
{
    public $timestamps = false;
    protected $fillable = [
        'patient_id', 'version', 'reviewed_by', 'reviewed_at',
        'signed_by_patient', 'signed_at', 'asa_classification',
        'is_current', 'notes', 'created_at'
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
        'signed_at' => 'datetime',
        'signed_by_patient' => 'boolean',
        'is_current' => 'boolean',
        'created_at' => 'datetime',
    ];

    public function patient(): BelongsTo { return $this->belongsTo(Patient::class); }
    public function reviewer(): BelongsTo { return $this->belongsTo(User::class, 'reviewed_by'); }
    public function conditions(): HasMany { return $this->hasMany(MedicalCondition::class); }
    public function responses(): HasMany { return $this->hasMany(MedicalHistoryResponse::class); }
}
