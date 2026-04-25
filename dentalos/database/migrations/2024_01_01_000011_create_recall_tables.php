<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recall_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->tinyInteger('default_interval_months')->default(6);
            $table->foreignId('associated_cdt_code_id')->nullable()->constrained('cdt_codes')->onDelete('set null');
            $table->string('color')->default('#27AE60');
            $table->boolean('is_active')->default(true);
            $table->boolean('send_reminder')->default(true);
            $table->json('reminder_days_before')->nullable();
            $table->timestamps();
        });

        Schema::create('patient_recalls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained('patients')->onDelete('cascade');
            $table->foreignId('recall_type_id')->constrained('recall_types')->onDelete('cascade');
            $table->foreignId('provider_id')->nullable()->constrained('users')->onDelete('set null');
            $table->date('due_date');
            $table->date('last_completed_date')->nullable();
            $table->foreignId('last_completed_appointment_id')->nullable()->constrained('appointments')->onDelete('set null');
            $table->enum('status', ['due', 'overdue', 'scheduled', 'completed', 'declined', 'inactive'])->default('due');
            $table->foreignId('appointment_id')->nullable()->constrained('appointments')->onDelete('set null');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patient_recalls');
        Schema::dropIfExists('recall_types');
    }
};
