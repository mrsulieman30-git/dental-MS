<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('message_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
            $table->string('name');
            $table->enum('category', ['appointment_reminder', 'recall', 'birthday', 'post_visit', 'review_request', 'payment', 'marketing', 'custom']);
            $table->enum('channel', ['sms', 'email', 'push', 'voice', 'whatsapp'])->default('sms');
            $table->string('subject')->nullable(); // for email
            $table->text('body_text');
            $table->longText('body_html')->nullable();
            $table->json('variables')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(false);
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });

        Schema::create('communication_log', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
            $table->foreignId('patient_id')->constrained('patients')->onDelete('cascade');
            $table->foreignId('template_id')->nullable()->constrained('message_templates')->onDelete('set null');
            $table->enum('channel', ['sms', 'email', 'push', 'voice', 'whatsapp']);
            $table->enum('direction', ['outbound', 'inbound'])->default('outbound');
            $table->string('from_address');
            $table->string('to_address');
            $table->string('subject')->nullable();
            $table->text('body');
            $table->enum('status', ['queued', 'sent', 'delivered', 'failed', 'opened', 'clicked', 'replied', 'bounced', 'opted_out'])->default('queued');
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('opened_at')->nullable();
            $table->timestamp('clicked_at')->nullable();
            $table->text('error_message')->nullable();
            $table->string('external_message_id')->nullable(); // Twilio SID / Mailgun ID
            $table->foreignId('appointment_id')->nullable()->constrained('appointments')->onDelete('set null');
            $table->timestamps();
        });

        Schema::create('automation_campaigns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
            $table->string('name');
            $table->enum('trigger_type', ['appointment_minus_7d', 'appointment_minus_3d', 'appointment_minus_1d', 'appointment_plus_1d', 'recall_due', 'birthday', 'new_patient', 'lapsed_patient', 'custom']);
            $table->boolean('is_active')->default(true);
            $table->foreignId('template_id')->constrained('message_templates')->onDelete('cascade');
            $table->integer('delay_hours')->default(0);
            $table->json('filter_conditions')->nullable();
            $table->json('stats')->nullable(); // {sent_count, open_rate}
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('automation_campaigns');
        Schema::dropIfExists('communication_log');
        Schema::dropIfExists('message_templates');
    }
};
