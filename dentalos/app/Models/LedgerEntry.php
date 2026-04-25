<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LedgerEntry extends Model
{
    protected $fillable = [
        'patient_id', 'location_id', 'entry_type', 'entry_date',
        'amount', 'description', 'cdt_code_id', 'appointment_id',
        'claim_id', 'payment_id', 'adjustment_type_id', 'created_by',
        'is_void', 'voided_at', 'voided_by', 'notes'
    ];

    protected $casts = [
        'entry_date' => 'date',
        'amount' => 'decimal:2',
        'is_void' => 'boolean',
        'voided_at' => 'datetime',
    ];

    public function patient(): BelongsTo { return $this->belongsTo(Patient::class); }
    public function location(): BelongsTo { return $this->belongsTo(Location::class); }
    public function cdtCode(): BelongsTo { return $this->belongsTo(CdtCode::class, 'cdt_code_id'); }
    public function appointment(): BelongsTo { return $this->belongsTo(Appointment::class); }
    public function claim(): BelongsTo { return $this->belongsTo(Claim::class); }
    public function payment(): BelongsTo { return $this->belongsTo(Payment::class); }
    public function adjustmentType(): BelongsTo { return $this->belongsTo(AdjustmentType::class); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
    public function voider(): BelongsTo { return $this->belongsTo(User::class, 'voided_by'); }
}
