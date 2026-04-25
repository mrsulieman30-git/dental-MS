<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiImageAnalysis extends Model
{
    protected $fillable = [
        'image_id', 'analysis_provider', 'analysis_version', 'findings',
        'analyzed_at', 'is_accepted_by_provider', 'accepted_at', 'rejected_findings'
    ];

    protected $casts = [
        'findings' => 'array',
        'rejected_findings' => 'array',
        'analyzed_at' => 'datetime',
        'accepted_at' => 'datetime',
        'is_accepted_by_provider' => 'boolean',
    ];

    public function image(): BelongsTo { return $this->belongsTo(DentalImage::class, 'image_id'); }
}
