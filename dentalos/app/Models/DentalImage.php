<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class DentalImage extends Model
{
    protected $fillable = [
        'series_id', 'patient_id', 'image_type', 'tooth_number',
        'image_number', 'file_path', 'thumbnail_path', 'file_format',
        'file_size_bytes', 'width_px', 'height_px', 'metadata',
        'ai_analysis_id', 'annotations', 'is_primary_for_claim'
    ];

    protected $casts = [
        'metadata' => 'array',
        'annotations' => 'array',
        'is_primary_for_claim' => 'boolean',
    ];

    public function series(): BelongsTo { return $this->belongsTo(ImagingSeries::class, 'series_id'); }
    public function patient(): BelongsTo { return $this->belongsTo(Patient::class); }
    public function aiAnalysis(): HasOne { return $this->hasOne(AiImageAnalysis::class, 'image_id'); }
}
