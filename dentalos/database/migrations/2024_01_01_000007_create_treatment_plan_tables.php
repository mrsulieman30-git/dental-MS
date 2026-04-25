<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('treatment_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained('patients')->onDelete('cascade');
            $table->foreignId('location_id')->constrained('locations')->onDelete('cascade');
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->string('name');
            $table->enum('status', ['draft', 'presented', 'accepted', 'in_progress', 'completed', 'declined', 'expired'])->default('draft');
            $table->timestamp('presented_at')->nullable();
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('declined_at')->nullable();
            $table->decimal('total_fee', 10, 2)->default(0);
            $table->decimal('insurance_estimated', 10, 2)->default(0);
            $table->decimal('patient_estimated', 10, 2)->default(0);
            $table->text('notes')->nullable();
            $table->date('expires_at')->nullable();
            $table->integer('version')->default(1);
            $table->foreignId('parent_plan_id')->nullable()->constrained('treatment_plans')->onDelete('set null');
            $table->boolean('signed_by_patient')->default(false);
            $table->timestamp('patient_signed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('treatment_plan_procedures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('treatment_plan_id')->constrained('treatment_plans')->onDelete('cascade');
            $table->tinyInteger('phase')->default(1);
            $table->integer('sequence_order')->default(0);
            $table->tinyInteger('tooth_number')->nullable();
            $table->json('surfaces')->nullable();
            $table->foreignId('cdt_code_id'); // We'll link this once cdt_codes table is created in next migration or here
            $table->string('procedure_name');
            $table->decimal('fee', 10, 2)->default(0);
            $table->decimal('insurance_estimated', 10, 2)->default(0);
            $table->decimal('patient_portion', 10, 2)->default(0);
            $table->enum('status', ['proposed', 'accepted', 'scheduled', 'completed', 'declined', 'referred'])->default('proposed');
            $table->enum('priority', ['immediate', 'soon', 'routine', 'elective'])->default('routine');
            $table->foreignId('appointment_id')->nullable()->constrained('appointments')->onDelete('set null');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('treatment_plan_procedures');
        Schema::dropIfExists('treatment_plans');
    }
};
