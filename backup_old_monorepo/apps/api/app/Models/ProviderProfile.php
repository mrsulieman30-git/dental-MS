<?php

namespace App\Models;

class ProviderProfile extends BaseModel
{
    protected $casts = [
        'license_expiry_date' => 'date',
        'dea_expiry_date' => 'date',
        'graduation_year' => 'integer',
        'production_goal_monthly' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
