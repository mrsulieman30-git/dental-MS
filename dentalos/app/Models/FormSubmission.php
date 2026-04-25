<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FormSubmission extends Model {
    protected $fillable = ['patient_id', 'template_id', 'appointment_id', 'submitted_at', 'completed_by', 'ip_address', 'responses', 'signature_path', 'is_locked', 'reviewed_by', 'reviewed_at'];
    protected $casts = ['submitted_at' => 'datetime', 'responses' => 'array', 'is_locked' => 'boolean', 'reviewed_at' => 'datetime'];
    public function patient(): BelongsTo { return $this->belongsTo(Patient::class); }
    public function template(): BelongsTo { return $this->belongsTo(FormTemplate::class); }
}
