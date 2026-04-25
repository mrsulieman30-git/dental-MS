<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PatientAlert extends Model
{
    protected $fillable = [
        'patient_id', 'type', 'message', 'severity', 'is_active',
        'show_at_checkin', 'expires_at', 'created_by'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'show_at_checkin' => 'boolean',
        'expires_at' => 'date',
    ];

    public function patient(): BelongsTo { return $this->belongsTo(Patient::class); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }

    public function scopeActive($query) { return $query->where('is_active', true)->where(fn($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now())); }
}
