<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Supplier extends Model {
    protected $fillable = ['tenant_id', 'name', 'account_number', 'contact_name', 'phone', 'email', 'website', 'address', 'ordering_portal_url', 'is_active'];
    protected $casts = ['address' => 'array', 'is_active' => 'boolean'];
}
