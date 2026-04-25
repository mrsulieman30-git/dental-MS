<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('form_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
            $table->string('name');
            $table->enum('form_type', ['medical_history', 'intake', 'consent', 'financial', 'custom']);
            $table->json('fields'); // full form definition
            $table->integer('version')->default(1);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_default_for_type')->default(false);
            $table->boolean('requires_signature')->default(false);
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });

        Schema::create('form_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained('patients')->onDelete('cascade');
            $table->foreignId('template_id')->constrained('form_templates')->onDelete('cascade');
            $table->foreignId('appointment_id')->nullable()->constrained('appointments')->onDelete('set null');
            $table->timestamp('submitted_at')->nullable();
            $table->enum('completed_by', ['patient', 'staff'])->default('staff');
            $table->string('ip_address')->nullable();
            $table->json('responses'); // {question_key => answer}
            $table->string('signature_path')->nullable();
            $table->boolean('is_locked')->default(false);
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('form_submissions');
        Schema::dropIfExists('form_templates');
    }
};
