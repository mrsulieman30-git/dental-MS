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
        Schema::create('patients', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->constrained('tenants')->onDelete('cascade');
            $table->string('patient_number', 50)->index();
            $table->string('first_name', 100);
            $table->string('middle_name', 100)->nullable();
            $table->string('last_name', 100);
            $table->string('preferred_name', 100)->nullable();
            $table->date('date_of_birth');
            $table->enum('gender', ['male', 'female', 'non_binary', 'prefer_not_to_say', 'other'])->default('prefer_not_to_say');
            $table->string('pronouns', 50)->nullable();
            $table->text('ssn_last4_encrypted')->nullable();
            $table->string('photo_url', 500)->nullable();
            $table->enum('status', ['active', 'inactive', 'deceased', 'transferred', 'prospective'])->default('active');
            
            $table->foreignUuid('primary_location_id')->constrained('locations');
            $table->foreignUuid('primary_provider_id')->constrained('users');
            $table->foreignUuid('primary_hygienist_id')->nullable()->constrained('users');
            $table->foreignUuid('responsible_party_id')->nullable()->constrained('patients'); // self-reference for minors
            
            $table->string('preferred_language', 50)->default('en');
            $table->enum('preferred_communication', ['sms', 'email', 'phone', 'portal', 'whatsapp'])->default('email');
            
            $table->boolean('is_new_patient')->default(true);
            $table->date('first_visit_date')->nullable();
            $table->date('last_visit_date')->nullable();
            $table->date('next_appointment_date')->nullable();
            $table->date('patient_since_date')->nullable();
            
            $table->enum('source', ['referral', 'walk_in', 'online', 'insurance', 'marketing', 'other'])->default('other');
            $table->foreignUuid('referred_by_patient_id')->nullable()->constrained('patients');
            $table->string('referred_by_source', 255)->nullable();
            
            $table->boolean('has_portal_account')->default(false);
            $table->string('portal_account_id', 100)->nullable()->index();
            
            $table->boolean('is_vip')->default(false);
            $table->boolean('needs_interpreter')->default(false);
            $table->boolean('has_special_needs')->default(false);
            $table->text('special_needs_notes')->nullable();
            $table->text('internal_notes')->nullable();
            $table->text('billing_notes')->nullable();
            
            $table->boolean('do_not_call')->default(false);
            $table->boolean('do_not_text')->default(false);
            $table->boolean('do_not_email')->default(false);
            
            $table->boolean('is_hipaa_signed')->default(false);
            $table->timestampTz('hipaa_signed_at')->nullable();
            $table->boolean('is_minor')->default(false);
            $table->string('guardian_name', 200)->nullable();
            $table->string('guardian_relationship', 100)->nullable();
            $table->string('guardian_phone', 50)->nullable();
            $table->timestampTz('portal_consent_signed_at')->nullable();
            
            $table->timestampsTz();
            $table->softDeletesTz();

            $table->unique(['tenant_id', 'patient_number']);
            $table->index('tenant_id');
            $table->index(['first_name', 'last_name']);
        });

        Schema::create('patient_contacts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('patient_id')->constrained('patients')->onDelete('cascade');
            $table->enum('type', ['mobile', 'home', 'work', 'emergency', 'other'])->default('mobile');
            $table->string('phone_number', 50);
            $table->string('label', 100)->nullable();
            $table->boolean('is_primary')->default(false);
            $table->boolean('is_sms_enabled')->default(true);
            $table->string('name', 200)->nullable(); // for emergency contacts
            $table->string('relationship', 100)->nullable();
            $table->timestampsTz();

            $table->index('patient_id');
        });

        Schema::create('patient_addresses', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('patient_id')->constrained('patients')->onDelete('cascade');
            $table->enum('type', ['home', 'billing', 'other'])->default('home');
            $table->string('address_line1', 255);
            $table->string('address_line2', 255)->nullable();
            $table->string('city', 100);
            $table->string('state', 100);
            $table->string('zip', 20);
            $table->string('country', 2)->default('US');
            $table->boolean('is_primary')->default(false);
            $table->timestampsTz();

            $table->index('patient_id');
        });

        Schema::create('patient_emails', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('patient_id')->constrained('patients')->onDelete('cascade');
            $table->string('email', 255);
            $table->enum('type', ['personal', 'work', 'other'])->default('personal');
            $table->boolean('is_primary')->default(false);
            $table->boolean('is_verified')->default(false);
            $table->timestampsTz();

            $table->index('patient_id');
        });

        Schema::create('patient_alerts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('patient_id')->constrained('patients')->onDelete('cascade');
            $table->enum('type', ['medical', 'billing', 'clinical', 'scheduling', 'custom'])->default('custom');
            $table->text('message');
            $table->enum('severity', ['info', 'warning', 'critical'])->default('info');
            $table->boolean('is_active')->default(true);
            $table->boolean('show_at_checkin')->default(true);
            $table->timestampTz('expires_at')->nullable();
            $table->foreignUuid('created_by')->constrained('users');
            $table->timestampsTz();

            $table->index('patient_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('patient_alerts');
        Schema::dropIfExists('patient_emails');
        Schema::dropIfExists('patient_addresses');
        Schema::dropIfExists('patient_contacts');
        Schema::dropIfExists('patients');
    }
};
