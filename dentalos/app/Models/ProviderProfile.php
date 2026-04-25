<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProviderProfile extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id', 'specialty', 'npi_number', 'license_number', 'license_state',
        'license_expiry_date', 'dea_number', 'dea_expiry_date', 'state_cds_number',
        'graduation_year', 'dental_school', 'bio', 'signature_image_path',
        'production_goal_monthly', 'schedule_color'
    ];

    protected $casts = [
        'license_expiry_date' => 'date',
        'dea_expiry_date' => 'date',
        'production_goal_monthly' => 'decimal:2',
    ];

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
}
