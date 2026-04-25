<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecallType extends Model
{
    protected $fillable = [
        'tenant_id', 'name', 'description', 'default_interval_months',
        'associated_cdt_code_id', 'color', 'is_active',
        'send_reminder', 'reminder_days_before'
    ];

    protected $casts = [
        'reminder_days_before' => 'array',
        'is_active' => 'boolean',
        'send_reminder' => 'boolean',
    ];

    public function tenant(): BelongsTo { return $this->belongsTo(Tenant::class); }
    public function cdtCode(): BelongsTo { return $this->belongsTo(CdtCode::class, 'associated_cdt_code_id'); }
}
