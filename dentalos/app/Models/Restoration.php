<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Restoration extends Model
{
    protected $fillable = [
        'dental_chart_id', 'appointment_id', 'tooth_number', 'surfaces',
        'restoration_type', 'material', 'shade', 'tooth_position',
        'bridge_teeth', 'lab_case_id', 'placement_date', 'placed_by',
        'notes', 'status'
    ];

    protected $casts = [
        'surfaces' => 'array',
        'bridge_teeth' => 'array',
        'placement_date' => 'date',
    ];

    public function chart(): BelongsTo { return $this->belongsTo(DentalChart::class, 'dental_chart_id'); }
    public function appointment(): BelongsTo { return $this->belongsTo(Appointment::class); }
    public function provider(): BelongsTo { return $this->belongsTo(User::class, 'placed_by'); }
}
