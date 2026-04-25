<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ToothCondition extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'dental_chart_id', 'appointment_id', 'tooth_number', 'surfaces',
        'condition_type', 'severity', 'status', 'cdt_code', 'procedure_id',
        'notes', 'diagnosed_date', 'treated_date', 'diagnosed_by', 'treated_by'
    ];

    protected $casts = [
        'surfaces' => 'array',
        'diagnosed_date' => 'date',
        'treated_date' => 'date',
    ];

    public function chart(): BelongsTo { return $this->belongsTo(DentalChart::class, 'dental_chart_id'); }
    public function appointment(): BelongsTo { return $this->belongsTo(Appointment::class); }
    public function diagnostician(): BelongsTo { return $this->belongsTo(User::class, 'diagnosed_by'); }
    public function treater(): BelongsTo { return $this->belongsTo(User::class, 'treated_by'); }
}
