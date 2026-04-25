<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenants', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->enum('plan_type', ['solo', 'group', 'dso', 'enterprise']);
            $table->enum('subscription_status', ['active', 'trialing', 'past_due', 'cancelled', 'suspended']);
            $table->timestamp('subscription_expires_at')->nullable();
            $table->integer('max_locations')->default(1);
            $table->integer('max_providers')->default(5);
            $table->integer('storage_limit_gb')->default(50);
            $table->boolean('is_active')->default(true);
            $table->json('metadata')->nullable();
            $table->json('branding')->nullable(); // logo_url, primary_color, secondary_color
            $table->string('timezone')->default('UTC');
            $table->string('country', 2);
            $table->string('billing_email');
            $table->json('billing_address')->nullable();
            $table->string('stripe_customer_id')->nullable();
            $table->string('stripe_subscription_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('locations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
            $table->string('name');
            $table->string('address_line1');
            $table->string('address_line2')->nullable();
            $table->string('city');
            $table->string('state');
            $table->string('zip');
            $table->string('country')->default('US');
            $table->string('phone');
            $table->string('fax')->nullable();
            $table->string('email')->nullable();
            $table->string('website')->nullable();
            $table->string('npi_number')->nullable();
            $table->string('tax_id')->nullable();
            $table->boolean('is_active')->default(true);
            $table->string('timezone');
            $table->json('business_hours'); // array of {day,open_time,close_time,is_closed}
            $table->integer('operatory_count')->default(4);
            $table->json('settings')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('locations');
        Schema::dropIfExists('tenants');
    }
};
