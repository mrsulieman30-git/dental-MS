<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AutomationCampaign extends Model
{
    protected $fillable = [
        'tenant_id', 'name', 'trigger_type', 'is_active', 'template_id',
        'delay_hours', 'filter_conditions', 'stats', 'created_by'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'filter_conditions' => 'array',
        'stats' => 'array',
    ];

    public function tenant(): BelongsTo { return $this->belongsTo(Tenant::class); }
    public function template(): BelongsTo { return $this->belongsTo(MessageTemplate::class); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
}
