<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tenant extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name', 'slug', 'plan_type', 'subscription_status', 'subscription_expires_at',
        'max_locations', 'max_providers', 'storage_limit_gb', 'is_active', 'metadata',
        'branding', 'timezone', 'country', 'billing_email', 'billing_address',
        'stripe_customer_id', 'stripe_subscription_id'
    ];

    protected $casts = [
        'subscription_expires_at' => 'datetime',
        'metadata' => 'array',
        'branding' => 'array',
        'billing_address' => 'array',
        'is_active' => 'boolean',
    ];

    public function locations(): HasMany { return $this->hasMany(Location::class); }
    public function users(): HasMany { return $this->hasMany(User::class); }
    public function patients(): HasMany { return $this->hasMany(Patient::class); }

    public function scopeActive($query) { return $query->where('is_active', true); }
}
