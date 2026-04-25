<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Era extends Model
{
    protected $fillable = [
        'tenant_id', 'clearinghouse_id', 'file_name', 'received_at',
        'payer_name', 'payer_id', 'check_number', 'check_date',
        'total_payment', 'is_posted', 'posted_at', 'posted_by', 'raw_data'
    ];

    protected $casts = [
        'received_at' => 'datetime',
        'check_date' => 'date',
        'total_payment' => 'decimal:2',
        'is_posted' => 'boolean',
        'posted_at' => 'datetime',
        'raw_data' => 'array',
    ];

    public function tenant(): BelongsTo { return $this->belongsTo(Tenant::class); }
    public function poster(): BelongsTo { return $this->belongsTo(User::class, 'posted_by'); }
}
