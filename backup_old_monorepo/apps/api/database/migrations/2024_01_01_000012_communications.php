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
        Schema::create('message_templates', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->constrained('tenants')->onDelete('cascade');
            $table->string('name', 200);
            $table->enum('category', [
                'appointment_reminder', 'recall', 'birthday', 'post_visit', 
                'review_request', 'payment', 'marketing', 'custom'
            ])->default('custom');
            $table->enum('channel', ['sms', 'email', 'push', 'voice', 'whatsapp'])->default('email');
            $table->string('subject', 255)->nullable(); // for email
            $table->text('body_text')->nullable();
            $table->text('body_html')->nullable();
            $table->json('variables')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(false);
            $table->foreignUuid('created_by')->constrained('users');
            $table->timestampsTz();

            $table->index('tenant_id');
            $table->index('category');
        });

        Schema::create('communication_log', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->constrained('tenants')->onDelete('cascade');
            $table->foreignUuid('patient_id')->constrained('patients')->onDelete('cascade');
            $table->foreignUuid('template_id')->nullable()->constrained('message_templates');
            $table->enum('channel', ['sms', 'email', 'push', 'voice', 'whatsapp']);
            $table->enum('direction', ['outbound', 'inbound'])->default('outbound');
            $table->string('from_address', 255)->nullable();
            $table->string('to_address', 255)->nullable();
            $table->string('subject', 255)->nullable();
            $table->text('body')->nullable();
            $table->enum('status', [
                'queued', 'sent', 'delivered', 'failed', 'opened', 
                'clicked', 'replied', 'bounced', 'opted_out'
            ])->default('queued');
            $table->timestampTz('sent_at')->nullable();
            $table->timestampTz('delivered_at')->nullable();
            $table->timestampTz('opened_at')->nullable();
            $table->timestampTz('clicked_at')->nullable();
            $table->text('error_message')->nullable();
            $table->string('external_message_id', 255)->nullable()->index(); // Twilio/Sendgrid
            $table->foreignUuid('appointment_id')->nullable()->constrained('appointments');
            $table->timestampTz('created_at')->useCurrent();

            $table->index('tenant_id');
            $table->index('patient_id');
            $table->index('created_at');
        });

        Schema::create('automation_campaigns', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->constrained('tenants')->onDelete('cascade');
            $table->string('name', 200);
            $table->enum('trigger_type', [
                'appointment_minus_7d', 'appointment_minus_3d', 'appointment_minus_1d', 
                'appointment_plus_1d', 'recall_due', 'birthday', 'new_patient', 
                'lapsed_patient', 'custom'
            ]);
            $table->boolean('is_active')->default(true);
            $table->foreignUuid('template_id')->constrained('message_templates');
            $table->integer('delay_hours')->default(0);
            $table->json('filter_conditions')->nullable();
            $table->json('stats')->nullable(); // sent_count, open_rate, etc.
            $table->foreignUuid('created_by')->constrained('users');
            $table->timestampsTz();

            $table->index('tenant_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('automation_campaigns');
        Schema::dropIfExists('communication_log');
        Schema::dropIfExists('message_templates');
    }
};
