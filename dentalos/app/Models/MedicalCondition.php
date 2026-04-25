<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MedicalCondition extends Model
{
    protected $fillable = [
        'medical_history_id', 'condition_name', 'icd10_code',
        'is_active', 'diagnosed_date', 'treating_physician', 'notes'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'diagnosed_date' => 'date',
    ];

    public function medicalHistory(): BelongsTo { return $this->belongsTo(MedicalHistory::class); }
}
