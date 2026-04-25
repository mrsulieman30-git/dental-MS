<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Payment extends Model
{
    protected $fillable = [
        'patient_id', 'location_id', 'payment_date', 'amount',
        'payment_method', 'card_last4', 'card_brand', 'check_number',
        'transaction_id', 'stripe_payment_intent_id', 'processor_response',
        'payment_plan_id', 'is_refunded', 'refund_amount',
        'refunded_at', 'received_by', 'notes'
    ];

    protected $casts = [
        'payment_date' => 'date',
        'amount' => 'decimal:2',
        'refund_amount' => 'decimal:2',
        'is_refunded' => 'boolean',
        'refunded_at' => 'datetime',
        'processor_response' => 'array',
    ];

    public function patient(): BelongsTo { return $this->belongsTo(Patient::class); }
    public function location(): BelongsTo { return $this->belongsTo(Location::class); }
    public function receiver(): BelongsTo { return $this->belongsTo(User::class, 'received_by'); }
    public function paymentPlan(): BelongsTo { return $this->belongsTo(PaymentPlan::class); }
    public function ledgerEntries(): HasMany { return $this->hasMany(LedgerEntry::class); }
}
