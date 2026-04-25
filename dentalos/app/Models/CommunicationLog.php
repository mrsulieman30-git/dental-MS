<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommunicationLog extends Model
{
    protected $table = 'communication_log';
    protected $fillable = [
        'tenant_id', 'patient_id', 'template_id', 'channel', 'direction',
        'from_address', 'to_address', 'subject', 'body', 'status',
        'sent_at', 'delivered_at', 'opened_at', 'clicked_at',
        'error_message', 'external_message_id', 'appointment_id'
    ];

    protected $casts = [
        'sent_at' => 'datetime',
        'delivered_at' => 'datetime',
        'opened_at' => 'datetime',
        'clicked_at' => 'datetime',
    ];

    public function tenant(): BelongsTo { return $this->belongsTo(Tenant::class); }
    public function patient(): BelongsTo { return $this->belongsTo(Patient::class); }
    public function template(): BelongsTo { return $this->belongsTo(MessageTemplate::class); }
    public function appointment(): BelongsTo { return $this->belongsTo(Appointment::class); }
}
