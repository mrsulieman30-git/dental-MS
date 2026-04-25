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
        Schema::create('users', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->constrained('tenants')->onDelete('cascade');
            $table->string('email', 255);
            $table->string('password_hash');
            $table->string('first_name', 100);
            $table->string('last_name', 100);
            $table->string('display_name', 200)->nullable();
            $table->string('avatar_url', 500)->nullable();
            $table->enum('role', [
                'super_admin', 'admin', 'doctor', 'hygienist', 
                'assistant', 'front_desk', 'billing', 'read_only'
            ])->default('read_only');
            $table->boolean('is_active')->default(true);
            $table->boolean('is_email_verified')->default(false);
            $table->timestampTz('email_verified_at')->nullable();
            $table->timestampTz('last_login_at')->nullable();
            $table->integer('failed_login_attempts')->default(0);
            $table->timestampTz('locked_until')->nullable();
            $table->boolean('mfa_enabled')->default(false);
            $table->string('mfa_secret')->nullable();
            $table->json('mfa_backup_codes')->nullable();
            $table->boolean('must_change_password')->default(true);
            $table->timestampsTz();
            $table->softDeletesTz();

            $table->unique(['tenant_id', 'email']);
            $table->index('tenant_id');
        });

        Schema::create('user_locations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignUuid('location_id')->constrained('locations')->onDelete('cascade');
            $table->boolean('is_primary')->default(false);
            $table->timestampsTz();

            $table->index(['user_id', 'location_id']);
        });

        Schema::create('user_sessions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained('users')->onDelete('cascade');
            $table->string('session_token', 255)->unique();
            $table->string('ip_address', 50)->nullable();
            $table->string('user_agent', 500)->nullable();
            $table->timestampTz('expires_at');
            $table->timestampsTz();

            $table->index('user_id');
            $table->index('expires_at');
        });

        Schema::create('audit_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->constrained('tenants')->onDelete('cascade');
            $table->foreignUuid('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('action', 100); // e.g. patient.view, chart.edit
            $table->string('resource_type', 100);
            $table->string('resource_id', 100)->nullable();
            $table->json('old_value')->nullable();
            $table->json('new_value')->nullable();
            $table->string('ip_address', 50)->nullable();
            $table->string('user_agent', 500)->nullable();
            $table->json('metadata')->nullable();
            $table->timestampTz('created_at')->useCurrent();

            $table->index('tenant_id');
            $table->index('user_id');
            $table->index(['resource_type', 'resource_id']);
        });

        Schema::create('provider_profiles', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->unique()->constrained('users')->onDelete('cascade');
            $table->string('specialty', 100)->nullable();
            $table->string('npi_number', 20)->nullable();
            $table->string('license_number', 50)->nullable();
            $table->string('license_state', 2)->nullable();
            $table->date('license_expiry_date')->nullable();
            $table->string('dea_number', 20)->nullable();
            $table->date('dea_expiry_date')->nullable();
            $table->string('state_cds_number', 20)->nullable();
            $table->integer('graduation_year')->nullable();
            $table->string('dental_school', 255)->nullable();
            $table->text('bio')->nullable();
            $table->string('signature_image_url', 500)->nullable();
            $table->decimal('production_goal_monthly', 15, 2)->default(0);
            $table->string('schedule_color', 7)->default('#0ea5e9');
            $table->timestampsTz();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('provider_profiles');
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('user_sessions');
        Schema::dropIfExists('user_locations');
        Schema::dropIfExists('users');
    }
};
