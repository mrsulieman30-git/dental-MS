<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Statement extends Model
{
    protected $fillable = [
        'patient_id', 'statement_date', 'due_date', 'balance_forward',
        'new_charges', 'payments_received', 'adjustments',
        'current_balance', 'status', 'sent_at', 'sent_via',
        'viewed_at', 'paid_at', 'file_path'
    ];

    protected $casts = [
        'statement_date' => 'date',
        'due_date' => 'date',
        'balance_forward' => 'decimal:2',
        'new_charges' => 'decimal:2',
        'payments_received' => 'decimal:2',
        'adjustments' => 'decimal:2',
        'current_balance' => 'decimal:2',
        'sent_at' => 'datetime',
        'sent_via' => 'array',
        'viewed_at' => 'datetime',
        'paid_at' => 'datetime',
    ];

    public function patient(): BelongsTo { return $this->belongsTo(Patient::class); }
}
