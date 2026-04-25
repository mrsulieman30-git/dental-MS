<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('labs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
            $table->string('name');
            $table->string('contact_name')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->json('address')->nullable();
            $table->string('account_number')->nullable();
            $table->tinyInteger('turnaround_days')->default(10);
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('lab_cases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
            $table->string('case_number');
            $table->foreignId('patient_id')->constrained('patients')->onDelete('cascade');
            $table->foreignId('location_id')->constrained('locations')->onDelete('cascade');
            $table->foreignId('lab_id')->constrained('labs')->onDelete('cascade');
            $table->foreignId('provider_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('appointment_id')->nullable()->constrained('appointments')->onDelete('set null');
            $table->foreignId('try_in_appointment_id')->nullable()->constrained('appointments')->onDelete('set null');
            $table->foreignId('seat_appointment_id')->nullable()->constrained('appointments')->onDelete('set null');
            $table->string('procedure_description');
            $table->foreignId('cdt_code_id')->nullable()->constrained('cdt_codes')->onDelete('set null');
            $table->json('tooth_numbers');
            $table->string('shade')->nullable();
            $table->string('material')->nullable();
            $table->text('specific_instructions')->nullable();
            $table->string('prescription_path')->nullable();
            $table->json('photos')->nullable();
            $table->enum('status', ['draft', 'sent', 'in_progress', 'ready', 'received', 'approved', 'returned', 'cancelled'])->default('draft');
            $table->date('sent_date')->nullable();
            $table->date('due_date')->nullable();
            $table->date('received_date')->nullable();
            $table->date('approved_date')->nullable();
            $table->string('lab_invoice_number')->nullable();
            $table->decimal('lab_cost', 10, 2)->nullable();
            $table->boolean('is_redo')->default(false);
            $table->text('redo_reason')->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lab_cases');
        Schema::dropIfExists('labs');
    }
};
