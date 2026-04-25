<?php

namespace App\Models;

use DentalOS\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

abstract class BaseModel extends Model
{
    use HasUuid, SoftDeletes;

    protected $guarded = [];

    protected $keyType = 'string';
    
    public $incrementing = false;

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'metadata' => 'json',
        'settings' => 'json',
        'branding' => 'json',
    ];
}
