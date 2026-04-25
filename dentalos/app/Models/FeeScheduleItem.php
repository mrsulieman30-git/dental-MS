<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FeeScheduleItem extends Model
{
    protected $fillable = [
        'fee_schedule_id', 'cdt_code_id', 'fee'
    ];

    protected $casts = [
        'fee' => 'decimal:2',
    ];

    public function feeSchedule(): BelongsTo { return $this->belongsTo(FeeSchedule::class); }
    public function cdtCode(): BelongsTo { return $this->belongsTo(CdtCode::class); }
}
