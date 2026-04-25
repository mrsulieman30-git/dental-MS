<?php

namespace App\Models;

class Tenant extends BaseModel
{
    protected $casts = [
        'metadata' => 'json',
        'branding' => 'json',
        'billing_address' => 'json',
        'subscription_expires_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function locations()
    {
        return $this->hasMany(Location::class);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }
}
