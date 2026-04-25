<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryItem extends Model {
    protected $fillable = ['tenant_id', 'sku', 'name', 'description', 'category', 'unit_of_measure', 'reorder_point', 'preferred_order_quantity', 'unit_cost', 'supplier_id', 'supplier_sku', 'is_controlled_substance', 'is_expiry_tracked', 'is_lot_tracked', 'is_active'];
    protected $casts = ['unit_cost' => 'decimal:2', 'reorder_point' => 'decimal:2', 'preferred_order_quantity' => 'decimal:2', 'is_controlled_substance' => 'boolean', 'is_expiry_tracked' => 'boolean', 'is_lot_tracked' => 'boolean', 'is_active' => 'boolean'];
    public function supplier(): BelongsTo { return $this->belongsTo(Supplier::class); }
}
