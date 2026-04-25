<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScheduleBlock extends Model
{
    protected $fillable = [
        'location_id', 'provider_id', 'operatory_id', 'title', 'block_type',
        'start_time', 'end_time', 'is_recurring', 'recurrence_rule', 'notes',
        'created_by'
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'is_recurring' => 'boolean',
        'recurrence_rule' => 'array',
    ];

    public function location(): BelongsTo { return $this->belongsTo(Location::class); }
    public function provider(): BelongsTo { return $this->belongsTo(User::class, 'provider_id'); }
    public function operatory(): BelongsTo { return $this->belongsTo(Operatory::class); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
}
