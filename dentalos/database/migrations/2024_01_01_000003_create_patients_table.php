<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('patients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
            $table->string('patient_number');
            $table->string('first_name');
            $table->string('middle_name')->nullable();
            $table->string('last_name');
            $table->string('preferred_name')->nullable();
            $table->date('date_of_birth');
            $table->enum('gender', ['male', 'female', 'non_binary', 'prefer_not_to_say', 'other']);
            $table->string('pronouns')->nullable();
            $table->string('ssn_last4', 4)->nullable();
            $table->string('photo_path')->nullable();
            $table->enum('status', ['active', 'inactive', 'deceased', 'transferred', 'prospective'])->default('active');
            $table->foreignId('primary_location_id')->nullable()->constrained('locations')->onDelete('set null');
            $table->foreignId('primary_provider_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('primary_hygienist_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('responsible_party_id')->nullable()->constrained('patients')->onDelete('set null');
            $table->string('preferred_language')->default('en');
            $table->enum('preferred_communication', ['sms', 'email', 'phone', 'portal', 'whatsapp'])->default('sms');
            $table->boolean('is_new_patient')->default(true);
            $table->date('first_visit_date')->nullable();
            $table->date('last_visit_date')->nullable();
            $table->dateTime('next_appointment_date')->nullable();
            $table->date('patient_since_date')->nullable();
            $table->enum('source', ['referral', 'walk_in', 'online', 'insurance', 'marketing', 'other'])->nullable();
            $table->foreignId('referred_by_patient_id')->nullable()->constrained('patients')->onDelete('set null');
            $table->string('referred_by_source')->nullable();
            $table->boolean('has_portal_account')->default(false);
            $table->foreignId('portal_user_id')->nullable()->constrained('users')->onDelete('set null');
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
            $table->timestamp('hipaa_signed_at')->nullable();
            $table->boolean('is_minor')->default(false);
            $table->string('guardian_name')->nullable();
            $table->string('guardian_relationship')->nullable();
            $table->string('guardian_phone')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['tenant_id', 'last_name', 'first_name']);
            $table->index(['tenant_id', 'patient_number']);
        });

        Schema::create('patient_contacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained('patients')->onDelete('cascade');
            $table->enum('type', ['mobile', 'home', 'work', 'emergency', 'other']);
            $table->string('phone_number');
            $table->string('label')->nullable();
            $table->boolean('is_primary')->default(false);
            $table->boolean('is_sms_enabled')->default(true);
            $table->string('contact_name')->nullable();
            $table->string('relationship')->nullable();
            $table->timestamps();
        });

        Schema::create('patient_addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained('patients')->onDelete('cascade');
            $table->enum('type', ['home', 'billing', 'other']);
            $table->string('address_line1');
            $table->string('address_line2')->nullable();
            $table->string('city');
            $table->string('state');
            $table->string('zip');
            $table->string('country')->default('US');
            $table->boolean('is_primary')->default(false);
            $table->timestamps();
        });

        Schema::create('patient_emails', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained('patients')->onDelete('cascade');
            $table->string('email');
            $table->enum('type', ['personal', 'work', 'other'])->default('personal');
            $table->boolean('is_primary')->default(false);
            $table->boolean('is_verified')->default(false);
            $table->timestamps();
        });

        Schema::create('patient_alerts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained('patients')->onDelete('cascade');
            $table->enum('type', ['medical', 'billing', 'clinical', 'scheduling', 'custom']);
            $table->text('message');
            $table->enum('severity', ['info', 'warning', 'critical'])->default('warning');
            $table->boolean('is_active')->default(true);
            $table->boolean('show_at_checkin')->default(true);
            $table->date('expires_at')->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patient_alerts');
        Schema::dropIfExists('patient_emails');
        Schema::dropIfExists('patient_addresses');
        Schema::dropIfExists('patient_contacts');
        Schema::dropIfExists('patients');
    }
};
