<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PaymentPlan extends Model
{
    protected $fillable = [
        'patient_id', 'total_amount', 'down_payment', 'installment_amount',
        'frequency', 'start_date', 'end_date', 'number_of_payments',
        'payments_made', 'remaining_balance', 'status',
        'auto_debit_enabled', 'auto_debit_stripe_payment_method_id',
        'auto_debit_day_of_month', 'financing_provider',
        'financing_account_number', 'notes', 'created_by'
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'down_payment' => 'decimal:2',
        'installment_amount' => 'decimal:2',
        'remaining_balance' => 'decimal:2',
        'start_date' => 'date',
        'end_date' => 'date',
        'auto_debit_enabled' => 'boolean',
    ];

    public function patient(): BelongsTo { return $this->belongsTo(Patient::class); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
    public function payments(): HasMany { return $this->hasMany(Payment::class); }
}
