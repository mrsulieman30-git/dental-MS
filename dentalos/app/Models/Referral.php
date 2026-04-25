<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Referral extends Model
{
    protected $fillable = [
        'tenant_id', 'patient_id', 'referring_provider_id',
        'receiving_provider_id', 'referral_contact_id', 'direction',
        'reason', 'cdt_codes', 'clinical_notes', 'referral_letter_path',
        'attachments', 'status', 'sent_date', 'due_back_date',
        'seen_date', 'completed_date', 'return_letter_path', 'response_notes'
    ];

    protected $casts = [
        'cdt_codes' => 'array',
        'attachments' => 'array',
        'sent_date' => 'date',
        'due_back_date' => 'date',
        'seen_date' => 'date',
        'completed_date' => 'date',
    ];

    public function tenant(): BelongsTo { return $this->belongsTo(Tenant::class); }
    public function patient(): BelongsTo { return $this->belongsTo(Patient::class); }
    public function referringProvider(): BelongsTo { return $this->belongsTo(User::class, 'referring_provider_id'); }
    public function receivingProvider(): BelongsTo { return $this->belongsTo(User::class, 'receiving_provider_id'); }
    public function referralContact(): BelongsTo { return $this->belongsTo(ReferralContact::class); }
}
