<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Operatory extends Model
{
    protected $fillable = [
        'location_id', 'name', 'room_number', 'operatory_type',
        'is_active', 'notes'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function location(): BelongsTo { return $this->belongsTo(Location::class); }
    public function appointments(): HasMany { return $this->hasMany(Appointment::class); }
}
