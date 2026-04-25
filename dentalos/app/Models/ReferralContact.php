<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ReferralContact extends Model
{
    protected $fillable = [
        'tenant_id', 'name', 'specialty', 'practice_name', 'phone', 'fax',
        'email', 'address', 'npi_number', 'is_preferred', 'notes'
    ];

    protected $casts = [
        'address' => 'array',
        'is_preferred' => 'boolean',
    ];

    public function tenant(): BelongsTo { return $this->belongsTo(Tenant::class); }
    public function referrals(): HasMany { return $this->hasMany(Referral::class); }
}
