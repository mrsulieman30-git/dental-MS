<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Location extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'tenant_id', 'name', 'address_line1', 'address_line2', 'city', 'state', 'zip',
        'country', 'phone', 'fax', 'email', 'website', 'npi_number', 'tax_id',
        'is_active', 'timezone', 'business_hours', 'operatory_count', 'settings'
    ];

    protected $casts = [
        'business_hours' => 'array',
        'settings' => 'array',
        'is_active' => 'boolean',
    ];

    public function tenant(): BelongsTo { return $this->belongsTo(Tenant::class); }
    public function users(): BelongsToMany { return $this->belongsToMany(User::class, 'user_locations'); }
    public function operatories(): HasMany { return $this->hasMany(Operatory::class); }

    public function scopeActive($query) { return $query->where('is_active', true); }
    public function scopeForTenant($query, $tenantId) { return $query->where('tenant_id', $tenantId); }
}
