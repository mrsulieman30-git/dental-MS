<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NoteTemplate extends Model
{
    protected $fillable = [
        'tenant_id', 'name', 'note_type', 'template_content', 'variables',
        'is_global', 'created_by', 'is_active', 'sort_order'
    ];

    protected $casts = [
        'variables' => 'array',
        'is_global' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function tenant(): BelongsTo { return $this->belongsTo(Tenant::class); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
}
