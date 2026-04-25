<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('appointment_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
            $table->string('name');
            $table->string('color')->default('#3498DB');
            $table->integer('default_duration_minutes')->default(60);
            $table->boolean('is_new_patient')->default(false);
            $table->boolean('requires_pre_auth')->default(false);
            $table->string('default_operatory_type')->nullable();
            $table->json('default_cdt_codes')->nullable(); // array of codes
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('operatories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('location_id')->constrained('locations')->onDelete('cascade');
            $table->string('name');
            $table->string('room_number')->nullable();
            $table->enum('operatory_type', ['general', 'hygiene', 'oral_surgery', 'orthodontics', 'consultation', 'xray', 'sterilization'])->default('general');
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
            $table->foreignId('location_id')->constrained('locations')->onDelete('cascade');
            $table->foreignId('patient_id')->constrained('patients')->onDelete('cascade');
            $table->foreignId('provider_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('hygienist_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('operatory_id')->nullable()->constrained('operatories')->onDelete('set null');
            $table->foreignId('appointment_type_id')->constrained('appointment_types')->onDelete('cascade');
            $table->dateTime('start_time');
            $table->dateTime('end_time');
            $table->integer('duration_minutes');
            $table->enum('status', ['scheduled', 'confirmed', 'checked_in', 'in_chair', 'completed', 'no_show', 'cancelled', 'broken', 'rescheduled'])->default('scheduled');
            $table->boolean('is_new_patient')->default(false);
            $table->text('notes')->nullable(); // patient-facing
            $table->text('internal_notes')->nullable();
            $table->enum('confirmation_status', ['unconfirmed', 'confirmed', 'declined'])->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->string('confirmed_by_method')->nullable();
            $table->timestamp('checked_in_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('arrival_time')->nullable();
            $table->decimal('production_estimated', 10, 2)->default(0);
            $table->boolean('is_recurring')->default(false);
            $table->char('recurring_group_id', 36)->nullable();
            $table->foreignId('parent_appointment_id')->nullable()->constrained('appointments')->onDelete('set null');
            $table->timestamp('cancelled_at')->nullable();
            $table->string('cancellation_reason')->nullable();
            $table->foreignId('cancelled_by')->nullable()->constrained('users')->onDelete('set null');
            $table->string('no_show_reason')->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['tenant_id', 'start_time']);
            $table->index(['patient_id', 'start_time']);
            $table->index(['provider_id', 'start_time']);
        });

        Schema::create('schedule_blocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('location_id')->constrained('locations')->onDelete('cascade');
            $table->foreignId('provider_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->foreignId('operatory_id')->nullable()->constrained('operatories')->onDelete('cascade');
            $table->string('title');
            $table->enum('block_type', ['lunch', 'meeting', 'vacation', 'ce', 'holiday', 'closure', 'other']);
            $table->dateTime('start_time');
            $table->dateTime('end_time');
            $table->boolean('is_recurring')->default(false);
            $table->json('recurrence_rule')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });

        Schema::create('waitlist', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
            $table->foreignId('patient_id')->constrained('patients')->onDelete('cascade');
            $table->foreignId('location_id')->constrained('locations')->onDelete('cascade');
            $table->foreignId('provider_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('appointment_type_id')->nullable()->constrained('appointment_types')->onDelete('set null');
            $table->json('preferred_days')->nullable();
            $table->time('preferred_time_start')->nullable();
            $table->time('preferred_time_end')->nullable();
            $table->enum('flexibility_level', ['exact', 'flexible', 'any'])->default('flexible');
            $table->text('notes')->nullable();
            $table->enum('status', ['waiting', 'notified', 'scheduled', 'removed'])->default('waiting');
            $table->timestamp('added_at')->nullable();
            $table->timestamp('contacted_at')->nullable();
            $table->foreignId('scheduled_appointment_id')->nullable()->constrained('appointments')->onDelete('set null');
            $table->timestamps();
        });

        Schema::create('appointment_reminders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('appointment_id')->constrained('appointments')->onDelete('cascade');
            $table->enum('reminder_type', ['sms', 'email', 'phone', 'push']);
            $table->datetime('scheduled_for');
            $table->timestamp('sent_at')->nullable();
            $table->enum('status', ['scheduled', 'sent', 'delivered', 'failed', 'opted_out'])->default('scheduled');
            $table->text('message_content')->nullable();
            $table->string('patient_response')->nullable();
            $table->timestamps();
        });
    }

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
