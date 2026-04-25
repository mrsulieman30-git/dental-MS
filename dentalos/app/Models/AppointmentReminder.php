<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AppointmentReminder extends Model
{
    protected $fillable = [
        'appointment_id', 'reminder_type', 'scheduled_for', 'sent_at',
        'status', 'message_content', 'patient_response'
    ];

    protected $casts = [
        'scheduled_for' => 'datetime',
        'sent_at' => 'datetime',
    ];

    public function appointment(): BelongsTo { return $this->belongsTo(Appointment::class); }
}
