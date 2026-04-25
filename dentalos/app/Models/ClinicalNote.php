<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClinicalNote extends Model
{
    protected $fillable = [
        'patient_id', 'appointment_id', 'provider_id', 'note_type',
        'subjective', 'objective', 'assessment', 'plan', 'full_note_text',
        'is_locked', 'locked_at', 'locked_by', 'template_id', 'is_signed',
        'signed_at', 'co_signed_by', 'co_signed_at', 'is_amended',
        'amendment_notes', 'version'
    ];

    protected $casts = [
        'is_locked' => 'boolean',
        'is_signed' => 'boolean',
        'is_amended' => 'boolean',
        'locked_at' => 'datetime',
        'signed_at' => 'datetime',
        'co_signed_at' => 'datetime',
    ];

    public function patient(): BelongsTo { return $this->belongsTo(Patient::class); }
    public function appointment(): BelongsTo { return $this->belongsTo(Appointment::class); }
    public function provider(): BelongsTo { return $this->belongsTo(User::class, 'provider_id'); }
    public function lockedBy(): BelongsTo { return $this->belongsTo(User::class, 'locked_by'); }
    public function template(): BelongsTo { return $this->belongsTo(NoteTemplate::class, 'template_id'); }
}
