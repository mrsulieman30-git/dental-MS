<?php

namespace App\Models;

use DentalOS\Traits\BelongsToTenant;
use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;

class User extends BaseModel implements
    AuthenticatableContract,
    AuthorizableContract
{
    use Authenticatable, Authorizable, HasApiTokens, Notifiable, BelongsToTenant;

    protected $hidden = [
        'password_hash',
        'mfa_secret',
        'mfa_backup_codes',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_login_at' => 'datetime',
        'locked_until' => 'datetime',
        'mfa_enabled' => 'boolean',
        'must_change_password' => 'boolean',
        'is_active' => 'boolean',
        'mfa_backup_codes' => 'json',
    ];

    /**
     * Get the password for the user.
     *
     * @return string
     */
    public function getAuthPassword()
    {
        return $this->password_hash;
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function locations()
    {
        return $this->belongsToMany(Location::class, 'user_locations', 'user_id', 'location_id')
            ->withPivot('is_primary')
            ->withTimestamps();
    }

    public function providerProfile()
    {
        return $this->hasOne(ProviderProfile::class);
    }
}
