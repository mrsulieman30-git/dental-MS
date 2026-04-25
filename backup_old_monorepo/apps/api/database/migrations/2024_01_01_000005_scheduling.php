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
        Schema::create('appointment_types', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->constrained('tenants')->onDelete('cascade');
            $table->string('name', 100);
            $table->string('color', 7)->default('#0ea5e9');
            $table->integer('default_duration_minutes')->default(60);
            $table->boolean('is_new_patient')->default(false);
            $table->boolean('requires_pre_auth')->default(false);
            $table->string('default_operatory_type', 50)->nullable();
            $table->json('cdt_codes')->nullable(); // Default codes for this type
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestampsTz();

            $table->index('tenant_id');
        });

        Schema::create('operatories', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('location_id')->constrained('locations')->onDelete('cascade');
            $table->string('name', 100);
            $table->string('room_number', 50)->nullable();
            $table->enum('operatory_type', [
                'general', 'hygiene', 'oral_surgery', 'orthodontics', 
                'consultation', 'xray', 'sterilization'
            ])->default('general');
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestampsTz();

            $table->index('location_id');
        });

        Schema::create('appointments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->constrained('tenants')->onDelete('cascade');
            $table->foreignUuid('location_id')->constrained('locations');
            $table->foreignUuid('patient_id')->constrained('patients');
            $table->foreignUuid('provider_id')->constrained('users');
            $table->foreignUuid('hygienist_id')->nullable()->constrained('users');
            $table->foreignUuid('operatory_id')->nullable()->constrained('operatories');
            $table->foreignUuid('appointment_type_id')->constrained('appointment_types');
            
            $table->timestampTz('start_time')->nullable();
            $table->timestampTz('end_time')->nullable();
            $table->integer('duration_minutes');
            
            $table->enum('status', [
                'scheduled', 'confirmed', 'checked_in', 'in_chair', 
                'completed', 'no_show', 'cancelled', 'broken', 'rescheduled'
            ])->default('scheduled');
            
            $table->boolean('is_new_patient')->default(false);
            $table->text('notes')->nullable(); // patient-facing
            $table->text('internal_notes')->nullable();
            
            $table->string('confirmation_status', 50)->nullable();
            $table->timestampTz('confirmed_at')->nullable();
            $table->string('confirmed_by_method', 50)->nullable();
            $table->timestampTz('checked_in_at')->nullable();
            $table->timestampTz('completed_at')->nullable();
            $table->timestampTz('arrival_time')->nullable();
            
            $table->decimal('production_estimated', 15, 2)->default(0);
            $table->boolean('is_recurring')->default(false);
            $table->uuid('recurring_group_id')->nullable()->index();
            $table->foreignUuid('parent_appointment_id')->nullable()->constrained('appointments');
            
            $table->timestampTz('cancelled_at')->nullable();
            $table->text('cancellation_reason')->nullable();
            $table->foreignUuid('cancelled_by')->nullable()->constrained('users');
            $table->text('no_show_reason')->nullable();
            
            $table->foreignUuid('created_by')->constrained('users');
            $table->timestampsTz();
            $table->softDeletesTz();

            $table->index('tenant_id');
            $table->index('location_id');
            $table->index('patient_id');
            $table->index(['start_time', 'end_time']);
        });

        Schema::create('schedule_blocks', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('location_id')->constrained('locations')->onDelete('cascade');
            $table->foreignUuid('provider_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->foreignUuid('operatory_id')->nullable()->constrained('operatories')->onDelete('cascade');
            $table->string('title', 200);
            $table->enum('block_type', [
                'lunch', 'meeting', 'vacation', 'ce', 'holiday', 'closure', 'other'
            ])->default('other');
            $table->timestampTz('start_time')->nullable();
            $table->timestampTz('end_time')->nullable();
            $table->boolean('is_recurring')->default(false);
            $table->json('recurrence_rule')->nullable();
            $table->text('notes')->nullable();
            $table->foreignUuid('created_by')->constrained('users');
            $table->timestampsTz();

            $table->index(['location_id', 'start_time']);
        });

        Schema::create('waitlist', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->constrained('tenants')->onDelete('cascade');
            $table->foreignUuid('patient_id')->constrained('patients')->onDelete('cascade');
            $table->foreignUuid('location_id')->constrained('locations');
            $table->foreignUuid('provider_id')->nullable()->constrained('users');
            $table->foreignUuid('appointment_type_id')->nullable()->constrained('appointment_types');
            
            $table->json('preferred_days')->nullable(); // [Mon, Tue...]
            $table->time('preferred_time_start')->nullable();
            $table->time('preferred_time_end')->nullable();
            $table->enum('flexibility_level', ['exact', 'flexible', 'any'])->default('any');
            
            $table->text('notes')->nullable();
            $table->enum('status', ['waiting', 'notified', 'scheduled', 'removed'])->default('waiting');
            
            $table->timestampTz('added_at')->useCurrent();
            $table->timestampTz('contacted_at')->nullable();
            $table->foreignUuid('scheduled_appointment_id')->nullable()->constrained('appointments');
            
            $table->timestampsTz();

            $table->index('tenant_id');
            $table->index('patient_id');
        });

        Schema::create('appointment_reminders', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('appointment_id')->constrained('appointments')->onDelete('cascade');
            $table->enum('reminder_type', ['sms', 'email', 'phone', 'push'])->default('sms');
            $table->timestampTz('scheduled_for')->nullable();
            $table->timestampTz('sent_at')->nullable();
            $table->enum('status', ['scheduled', 'sent', 'delivered', 'failed', 'opted_out'])->default('scheduled');
            $table->text('message_content')->nullable();
            $table->text('patient_response')->nullable();
            $table->timestampTz('created_at')->useCurrent();

            $table->index(['appointment_id', 'scheduled_for']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appointment_reminders');
        Schema::dropIfExists('waitlist');
        Schema::dropIfExists('schedule_blocks');
        Schema::dropIfExists('appointments');
        Schema::dropIfExists('operatories');
        Schema::dropIfExists('appointment_types');
    }
};
