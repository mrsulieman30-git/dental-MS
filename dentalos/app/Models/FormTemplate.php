<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FormTemplate extends Model {
    protected $fillable = ['tenant_id', 'name', 'form_type', 'fields', 'version', 'is_active', 'is_default_for_type', 'requires_signature', 'created_by'];
    protected $casts = ['fields' => 'array', 'is_active' => 'boolean', 'is_default_for_type' => 'boolean', 'requires_signature' => 'boolean'];
    public function tenant(): BelongsTo { return $this->belongsTo(Tenant::class); }
}
