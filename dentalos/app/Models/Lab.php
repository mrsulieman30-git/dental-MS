<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Lab extends Model
{
    protected $fillable = [
        'tenant_id', 'name', 'contact_name', 'phone', 'email', 'address',
        'account_number', 'turnaround_days', 'notes', 'is_active'
    ];

    protected $casts = [
        'address' => 'array',
        'is_active' => 'boolean',
    ];

    public function tenant(): BelongsTo { return $this->belongsTo(Tenant::class); }
    public function labCases(): HasMany { return $this->hasMany(LabCase::class); }
}
