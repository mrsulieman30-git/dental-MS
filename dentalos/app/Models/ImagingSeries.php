<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ImagingSeries extends Model
{
    protected $fillable = [
        'patient_id', 'appointment_id', 'series_type', 'name',
        'taken_at', 'taken_by', 'device_id', 'notes', 'is_archived'
    ];

    protected $casts = [
        'taken_at' => 'datetime',
        'is_archived' => 'boolean',
    ];

    public function patient(): BelongsTo { return $this->belongsTo(Patient::class); }
    public function appointment(): BelongsTo { return $this->belongsTo(Appointment::class); }
    public function images(): HasMany { return $this->hasMany(DentalImage::class, 'series_id'); }
    public function taker(): BelongsTo { return $this->belongsTo(User::class, 'taken_by'); }
}
