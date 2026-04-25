<?php

namespace App\Models;

use DentalOS\Traits\BelongsToTenant;

class Location extends BaseModel
{
    use BelongsToTenant;

    protected $casts = [
        'business_hours' => 'json',
        'settings' => 'json',
        'is_active' => 'boolean',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
}
