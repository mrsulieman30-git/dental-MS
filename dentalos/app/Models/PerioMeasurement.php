<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PerioMeasurement extends Model
{
    public $timestamps = false;
    protected $fillable = [
        'perio_chart_id', 'tooth_number', 'surface', 'pos1_probe', 'pos2_probe',
        'pos3_probe', 'pos1_recession', 'pos2_recession', 'pos3_recession',
        'pos1_bleeding', 'pos2_bleeding', 'pos3_bleeding', 'pos1_suppuration',
        'pos2_suppuration', 'pos3_suppuration', 'furcation_class',
        'mobility_grade', 'plaque_present', 'calculus_present', 'created_at'
    ];

    protected $casts = [
        'pos1_bleeding' => 'boolean',
        'pos2_bleeding' => 'boolean',
        'pos3_bleeding' => 'boolean',
        'pos1_suppuration' => 'boolean',
        'pos2_suppuration' => 'boolean',
        'pos3_suppuration' => 'boolean',
        'plaque_present' => 'boolean',
        'calculus_present' => 'boolean',
        'created_at' => 'datetime',
    ];

    public function chart(): BelongsTo { return $this->belongsTo(PerioChart::class, 'perio_chart_id'); }
}
