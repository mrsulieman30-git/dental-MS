<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CdtCode extends Model
{
    public $timestamps = false;
    protected $fillable = [
        'code', 'description', 'short_description', 'category', 'is_active', 'created_at'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'created_at' => 'datetime',
    ];
}
