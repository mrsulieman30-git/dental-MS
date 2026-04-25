<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClaimAttachment extends Model
{
    protected $fillable = [
        'claim_id', 'attachment_type', 'file_path', 'file_name',
        'file_size_bytes', 'created_by'
    ];

    public function claim(): BelongsTo { return $this->belongsTo(Claim::class); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
}
