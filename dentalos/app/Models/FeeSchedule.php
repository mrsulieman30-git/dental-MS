<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FeeSchedule extends Model
{
    protected $fillable = [
        'tenant_id', 'carrier_id', 'name', 'type', 'effective_date',
        'expiry_date', 'is_active'
    ];

    protected $casts = [
        'effective_date' => 'date',
        'expiry_date' => 'date',
        'is_active' => 'boolean',
    ];

    public function tenant(): BelongsTo { return $this->belongsTo(Tenant::class); }
    public function carrier(): BelongsTo { return $this->belongsTo(InsuranceCarrier::class, 'carrier_id'); }
    public function items(): HasMany { return $this->hasMany(FeeScheduleItem::class); }
}
