<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('suppliers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->constrained('tenants')->onDelete('cascade');
            $table->string('name', 255);
            $table->string('account_number', 100)->nullable();
            $table->string('contact_name', 150)->nullable();
            $table->string('phone', 50)->nullable();
            $table->string('email', 255)->nullable();
            $table->string('website', 255)->nullable();
            $table->json('address')->nullable();
            $table->string('ordering_portal_url', 500)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestampsTz();

            $table->index('tenant_id');
        });

        Schema::create('inventory_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->constrained('tenants')->onDelete('cascade');
            $table->string('sku', 100)->nullable()->index();
            $table->string('name', 255);
            $table->text('description')->nullable();
            $table->enum('category', [
                'consumable', 'medication', 'instrument', 'equipment', 'office_supply', 'other'
            ])->default('consumable');
            $table->string('unit_of_measure', 50)->default('unit');
            $table->decimal('reorder_point', 15, 2)->default(0);
            $table->decimal('preferred_order_quantity', 15, 2)->default(0);
            $table->decimal('unit_cost', 15, 2)->default(0);
            $table->foreignUuid('supplier_id')->nullable()->constrained('suppliers')->onDelete('set null');
            $table->string('supplier_sku', 100)->nullable();
            $table->boolean('is_controlled_substance')->default(false);
            $table->boolean('is_expiry_tracked')->default(false);
            $table->boolean('is_lot_tracked')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestampsTz();

            $table->index('tenant_id');
        });

        Schema::create('inventory_locations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('inventory_item_id')->constrained('inventory_items')->onDelete('cascade');
            $table->foreignUuid('location_id')->constrained('locations')->onDelete('cascade');
            $table->decimal('current_quantity', 15, 2)->default(0);
            $table->decimal('minimum_quantity', 15, 2)->default(0);
            $table->timestampsTz();

            $table->index(['inventory_item_id', 'location_id']);
        });

        Schema::create('inventory_transactions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('inventory_item_id')->constrained('inventory_items')->onDelete('cascade');
            $table->foreignUuid('location_id')->constrained('locations')->onDelete('cascade');
            $table->enum('transaction_type', [
                'receive', 'use', 'adjust', 'transfer', 'return', 'expire', 'dispose'
            ]);
            $table->decimal('quantity', 15, 2);
            $table->decimal('unit_cost', 15, 2)->nullable();
            $table->string('lot_number', 100)->nullable();
            $table->date('expiry_date')->nullable();
            $table->uuid('reference_id')->nullable()->index(); // points to PO, appointment etc
            $table->string('reference_type', 100)->nullable();
            $table->text('notes')->nullable();
            $table->foreignUuid('performed_by')->constrained('users');
            $table->timestampTz('created_at')->useCurrent();

            $table->index('inventory_item_id');
            $table->index('created_at');
        });

        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->constrained('tenants')->onDelete('cascade');
            $table->foreignUuid('location_id')->constrained('locations');
            $table->string('po_number', 100)->index();
            $table->foreignUuid('supplier_id')->constrained('suppliers');
            $table->enum('status', ['draft', 'submitted', 'acknowledged', 'partial', 'received', 'cancelled'])->default('draft');
            $table->timestampTz('ordered_at')->nullable();
            $table->date('expected_delivery_date')->nullable();
            $table->timestampTz('received_at')->nullable();
            $table->decimal('total_cost', 15, 2)->default(0);
            $table->string('invoice_number', 100)->nullable();
            $table->string('invoice_url', 500)->nullable();
            $table->text('notes')->nullable();
            $table->foreignUuid('created_by')->constrained('users');
            $table->timestampsTz();

            $table->index('tenant_id');
        });

        Schema::create('purchase_order_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('purchase_order_id')->constrained('purchase_orders')->onDelete('cascade');
            $table->foreignUuid('inventory_item_id')->constrained('inventory_items');
            $table->decimal('quantity_ordered', 15, 2);
            $table->decimal('quantity_received', 15, 2)->default(0);
            $table->decimal('unit_cost', 15, 2);
            $table->decimal('total_cost', 15, 2);
            $table->string('lot_number', 100)->nullable();
            $table->date('expiry_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestampTz('created_at')->useCurrent();

            $table->index('purchase_order_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_order_items');
        Schema::dropIfExists('purchase_orders');
        Schema::dropIfExists('inventory_transactions');
        Schema::dropIfExists('inventory_locations');
        Schema::dropIfExists('inventory_items');
        Schema::dropIfExists('suppliers');
    }
};
