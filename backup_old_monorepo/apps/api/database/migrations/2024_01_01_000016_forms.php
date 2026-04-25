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
        Schema::create('form_templates', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->constrained('tenants')->onDelete('cascade');
            $table->string('name', 200);
            $table->enum('form_type', ['medical_history', 'intake', 'consent', 'financial', 'custom'])->default('custom');
            $table->json('fields'); // full form definition
            $table->integer('version')->default(1);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_default_for_type')->default(false);
            $table->boolean('requires_signature')->default(true);
            $table->foreignUuid('created_by')->constrained('users');
            $table->timestampsTz();

            $table->index('tenant_id');
        });

        Schema::create('form_submissions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('patient_id')->constrained('patients')->onDelete('cascade');
            $table->foreignUuid('template_id')->constrained('form_templates');
            $table->foreignUuid('appointment_id')->nullable()->constrained('appointments');
            $table->timestampTz('submitted_at')->nullable();
            $table->enum('completed_by', ['patient', 'staff'])->default('patient');
            $table->string('ip_address', 50)->nullable();
            $table->string('user_agent', 500)->nullable();
            $table->json('responses');
            $table->string('signature_url', 500)->nullable();
            $table->boolean('is_locked')->default(false);
            $table->foreignUuid('reviewed_by')->nullable()->constrained('users');
            $table->timestampTz('reviewed_at')->nullable();
            $table->timestampTz('created_at')->useCurrent();

            $table->index('patient_id');
            $table->index('template_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('form_submissions');
        Schema::dropIfExists('form_templates');
    }
};
