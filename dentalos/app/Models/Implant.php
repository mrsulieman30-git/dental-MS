<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Implant extends Model
{
    protected $fillable = [
        'dental_chart_id', 'tooth_number', 'implant_system', 'implant_brand',
        'fixture_diameter', 'fixture_length', 'placement_date', 'placed_by',
        'placement_location_id', 'restoration_date', 'crown_material',
        'abutment_type', 'torque_value', 'bone_graft_material',
        'membrane_used', 'lot_number', 'serial_number', 'implant_status', 'notes'
    ];

    protected $casts = [
        'fixture_diameter' => 'decimal:2',
        'fixture_length' => 'decimal:2',
        'torque_value' => 'decimal:2',
        'placement_date' => 'date',
        'restoration_date' => 'date',
        'membrane_used' => 'boolean',
    ];

    public function chart(): BelongsTo { return $this->belongsTo(DentalChart::class, 'dental_chart_id'); }
    public function provider(): BelongsTo { return $this->belongsTo(User::class, 'placed_by'); }
    public function location(): BelongsTo { return $this->belongsTo(Location::class, 'placement_location_id'); }
}
