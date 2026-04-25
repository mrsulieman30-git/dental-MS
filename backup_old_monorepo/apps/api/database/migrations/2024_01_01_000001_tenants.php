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
        Schema::create('tenants', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name', 255);
            $table->string('slug', 100)->unique();
            $table->enum('plan_type', ['solo', 'group', 'dso', 'enterprise'])->default('solo');
            $table->string('subscription_status', 50)->default('trial');
            $table->timestampTz('subscription_expires_at')->nullable();
            $table->integer('max_locations')->default(1);
            $table->integer('max_providers')->default(2);
            $table->integer('storage_limit_gb')->default(5);
            $table->boolean('is_active')->default(true);
            $table->json('metadata')->nullable();
            $table->json('branding')->nullable(); // logo_url, primary_color, secondary_color
            $table->string('timezone', 100)->default('UTC');
            $table->string('country', 2)->default('US');
            $table->string('billing_email', 255)->nullable();
            $table->json('billing_address')->nullable();
            $table->string('stripe_customer_id', 255)->nullable()->index();
            $table->string('stripe_subscription_id', 255)->nullable()->index();
            $table->timestampsTz();
        });

        Schema::create('locations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->constrained('tenants')->onDelete('cascade');
            $table->string('slug', 100)->nullable()->index();
            $table->string('name', 255);
            $table->string('address_line1', 255);
            $table->string('address_line2', 255)->nullable();
            $table->string('city', 100);
            $table->string('state', 100);
            $table->string('zip', 20);
            $table->string('country', 2)->default('US');
            $table->string('phone', 50)->nullable();
            $table->string('fax', 50)->nullable();
            $table->string('email', 255)->nullable();
            $table->string('website', 255)->nullable();
            $table->string('npi_number', 20)->nullable();
            $table->string('tax_id', 20)->nullable();
            $table->boolean('is_active')->default(true);
            $table->string('timezone', 100)->default('UTC');
            $table->json('business_hours')->nullable(); // [{day, open_time, close_time, is_closed}]
            $table->integer('operatory_count')->default(1);
            $table->json('settings')->nullable();
            $table->timestampsTz();
            $table->softDeletesTz();

            $table->index('tenant_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('locations');
        Schema::dropIfExists('tenants');
    }
};
