<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdjustmentType extends Model
{
    protected $fillable = [
        'tenant_id', 'name', 'code', 'is_debit', 'affects_production',
        'affects_collections', 'requires_approval', 'is_active'
    ];

    protected $casts = [
        'is_debit' => 'boolean',
        'affects_production' => 'boolean',
        'affects_collections' => 'boolean',
        'requires_approval' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function tenant(): BelongsTo { return $this->belongsTo(Tenant::class); }
}
