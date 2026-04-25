<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'tenant_id', 'email', 'password', 'first_name', 'last_name', 'display_name',
        'avatar_url', 'role', 'is_active', 'email_verified_at', 'last_login_at',
        'failed_login_attempts', 'locked_until', 'mfa_enabled', 'mfa_secret',
        'mfa_backup_codes', 'must_change_password'
    ];

    protected $hidden = [
        'password', 'remember_token', 'mfa_secret',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'last_login_at' => 'datetime',
        'locked_until' => 'datetime',
        'mfa_enabled' => 'boolean',
        'mfa_backup_codes' => 'array',
        'must_change_password' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function tenant(): BelongsTo { return $this->belongsTo(Tenant::class); }
    public function profile(): HasOne { return $this->hasOne(ProviderProfile::class); }
    public function locations(): BelongsToMany { return $this->belongsToMany(Location::class, 'user_locations'); }
    public function appointments(): HasMany { return $this->hasMany(Appointment::class, 'provider_id'); }

    public function getFullNameAttribute(): string { return "{$this->first_name} {$this->last_name}"; }

    public function scopeActive($query) { return $query->where('is_active', true); }
    public function scopeForTenant($query, $tenantId) { return $query->where('tenant_id', $tenantId); }
}
