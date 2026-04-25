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
        Schema::create('labs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->constrained('tenants')->onDelete('cascade');
            $table->string('name', 255);
            $table->string('contact_name', 150)->nullable();
            $table->string('phone', 50)->nullable();
            $table->string('email', 255)->nullable();
            $table->json('address')->nullable();
            $table->string('account_number', 100)->nullable();
            $table->integer('turnaround_days')->default(10);
            $table->integer('rating')->default(5); // 1-5
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestampsTz();

            $table->index('tenant_id');
        });

        Schema::create('lab_cases', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->constrained('tenants')->onDelete('cascade');
            $table->string('case_number', 100)->index();
            $table->foreignUuid('patient_id')->constrained('patients');
            $table->foreignUuid('location_id')->constrained('locations');
            $table->foreignUuid('lab_id')->constrained('labs');
            $table->foreignUuid('provider_id')->constrained('users');
            $table->foreignUuid('appointment_id')->nullable()->constrained('appointments');
            $table->foreignUuid('try_in_appointment_id')->nullable()->constrained('appointments');
            $table->foreignUuid('seat_appointment_id')->nullable()->constrained('appointments');
            
            $table->text('procedure_description');
            $table->string('cdt_code', 20)->nullable();
            $table->json('tooth_numbers')->nullable();
            $table->string('shade', 50)->nullable();
            $table->string('material', 100)->nullable();
            $table->text('specific_instructions')->nullable();
            $table->string('prescription_url', 500)->nullable();
            $table->json('photos')->nullable();
            
            $table->enum('status', [
                'draft', 'sent', 'in_progress', 'ready', 
                'received', 'approved', 'returned', 'cancelled'
            ])->default('draft');
            
            $table->date('sent_date')->nullable();
            $table->date('due_date')->nullable();
            $table->date('received_date')->nullable();
            $table->date('approved_date')->nullable();
            
            $table->string('lab_invoice_number', 100)->nullable();
            $table->decimal('lab_cost', 15, 2)->default(0);
            $table->boolean('is_redo')->default(false);
            $table->text('redo_reason')->nullable();
            
            $table->foreignUuid('created_by')->constrained('users');
            $table->timestampsTz();
            $table->softDeletesTz();

            $table->index('tenant_id');
            $table->index('patient_id');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lab_cases');
        Schema::dropIfExists('labs');
    }
};
