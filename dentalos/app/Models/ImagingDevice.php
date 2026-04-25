<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ImagingDevice extends Model
{
    protected $fillable = [
        'location_id', 'name', 'manufacturer', 'model', 'serial_number',
        'device_type', 'twain_name', 'is_active', 'calibration_date'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'calibration_date' => 'date',
    ];

    public function location(): BelongsTo { return $this->belongsTo(Location::class); }
}
