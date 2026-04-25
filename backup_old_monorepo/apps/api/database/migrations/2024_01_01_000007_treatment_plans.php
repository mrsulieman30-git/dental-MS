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
        Schema::create('treatment_plans', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('patient_id')->constrained('patients')->onDelete('cascade');
            $table->foreignUuid('location_id')->constrained('locations');
            $table->foreignUuid('created_by')->constrained('users');
            $table->string('name', 200);
            $table->enum('status', [
                'draft', 'presented', 'accepted', 'in_progress', 
                'completed', 'declined', 'expired'
            ])->default('draft');
            $table->timestampTz('presented_at')->nullable();
            $table->timestampTz('accepted_at')->nullable();
            $table->timestampTz('declined_at')->nullable();
            $table->decimal('total_fee', 15, 2)->default(0);
            $table->decimal('insurance_estimated', 15, 2)->default(0);
            $table->decimal('patient_estimated', 15, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestampTz('expires_at')->nullable();
            $table->integer('version')->default(1);
            $table->foreignUuid('parent_plan_id')->nullable()->constrained('treatment_plans'); // for alternatives
            $table->boolean('signed_by_patient')->default(false);
            $table->timestampTz('patient_signed_at')->nullable();
            $table->timestampsTz();
            $table->softDeletesTz();

            $table->index('patient_id');
            $table->index('status');
        });

        Schema::create('treatment_plan_procedures', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('treatment_plan_id')->constrained('treatment_plans')->onDelete('cascade');
            $table->integer('phase')->default(1);
            $table->integer('sequence_order')->default(1);
            $table->integer('tooth_number')->nullable();
            $table->json('surfaces')->nullable();
            $table->string('cdt_code', 20); // FK will be in insurance migration
            $table->string('procedure_name', 255);
            $table->decimal('fee', 15, 2)->default(0);
            $table->decimal('insurance_estimated', 15, 2)->default(0);
            $table->decimal('patient_portion', 15, 2)->default(0);
            $table->enum('status', [
                'proposed', 'accepted', 'scheduled', 'completed', 'declined', 'referred'
            ])->default('proposed');
            $table->enum('priority', ['immediate', 'soon', 'routine', 'elective'])->default('routine');
            $table->foreignUuid('appointment_id')->nullable()->constrained('appointments');
            $table->text('notes')->nullable();
            $table->timestampsTz();

            $table->index('treatment_plan_id');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('treatment_plan_procedures');
        Schema::dropIfExists('treatment_plans');
    }
};
