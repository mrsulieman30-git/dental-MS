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
        Schema::create('dental_charts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('patient_id')->constrained('patients')->onDelete('cascade');
            $table->timestampTz('created_at')->useCurrent();

            $table->index('patient_id');
        });

        Schema::create('tooth_conditions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('dental_chart_id')->constrained('dental_charts')->onDelete('cascade');
            $table->foreignUuid('appointment_id')->nullable()->constrained('appointments');
            $table->integer('tooth_number'); // 1-32
            $table->json('surfaces')->nullable(); // M|D|O|B|L|I|F
            $table->enum('condition_type', [
                'caries', 'fracture', 'wear', 'sensitivity', 'mobility', 
                'peri_implantitis', 'perio', 'other'
            ])->default('caries');
            $table->enum('severity', ['initial', 'moderate', 'severe', 'watch'])->default('watch');
            $table->enum('status', [
                'existing', 'proposed', 'in_progress', 'completed', 
                'declined', 'referred', 'monitored'
            ])->default('proposed');
            $table->string('cdt_code', 20)->nullable();
            $table->uuid('procedure_id')->nullable(); // FK to procedure table if applicable
            $table->text('notes')->nullable();
            $table->date('diagnosed_date')->nullable();
            $table->date('treated_date')->nullable();
            $table->foreignUuid('diagnosed_by')->nullable()->constrained('users');
            $table->foreignUuid('treated_by')->nullable()->constrained('users');
            $table->timestampsTz();
            $table->softDeletesTz();

            $table->index('dental_chart_id');
            $table->index(['tooth_number', 'status']);
        });

        Schema::create('restorations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('dental_chart_id')->constrained('dental_charts')->onDelete('cascade');
            $table->foreignUuid('appointment_id')->nullable()->constrained('appointments');
            $table->integer('tooth_number');
            $table->json('surfaces')->nullable();
            $table->enum('restoration_type', [
                'filling', 'crown', 'bridge', 'implant', 'veneer', 'onlay', 'inlay', 
                'denture_partial', 'denture_full', 'sealant', 'rct', 'buildup', 'post_core', 'other'
            ])->default('filling');
            $table->enum('material', [
                'amalgam', 'composite', 'gold', 'porcelain', 'zirconia', 'pfm', 'acrylic', 'other'
            ])->nullable();
            $table->string('shade', 20)->nullable();
            $table->enum('tooth_position', ['abutment', 'pontic'])->nullable();
            $table->json('bridge_teeth')->nullable(); // of ints
            $table->uuid('lab_case_id')->nullable();
            $table->date('placement_date')->nullable();
            $table->foreignUuid('placed_by')->nullable()->constrained('users');
            $table->text('notes')->nullable();
            $table->enum('status', ['existing', 'new', 'needs_replacement', 'failed'])->default('new');
            $table->timestampsTz();

            $table->index('dental_chart_id');
        });

        Schema::create('implants', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('dental_chart_id')->constrained('dental_charts')->onDelete('cascade');
            $table->integer('tooth_number');
            $table->string('implant_system', 100)->nullable();
            $table->string('implant_brand', 100)->nullable();
            $table->string('fixture_diameter', 50)->nullable();
            $table->string('fixture_length', 50)->nullable();
            $table->date('placement_date')->nullable();
            $table->foreignUuid('placed_by')->nullable()->constrained('users');
            $table->foreignUuid('placement_location_id')->nullable()->constrained('locations');
            $table->date('restoration_date')->nullable();
            $table->string('crown_material', 100)->nullable();
            $table->string('abutment_type', 100)->nullable();
            $table->string('torque_value', 50)->nullable();
            $table->string('bone_graft_material', 100)->nullable();
            $table->string('membrane_used', 100)->nullable();
            $table->string('lot_number', 100)->nullable();
            $table->string('serial_number', 100)->nullable();
            $table->enum('implant_status', ['active', 'healing', 'failed', 'removed'])->default('active');
            $table->text('notes')->nullable();
            $table->timestampsTz();

            $table->index('dental_chart_id');
        });

        Schema::create('perio_charts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('patient_id')->constrained('patients')->onDelete('cascade');
            $table->foreignUuid('appointment_id')->constrained('appointments')->onDelete('cascade');
            $table->foreignUuid('provider_id')->constrained('users');
            $table->date('chart_date');
            $table->enum('aap_stage', ['I', 'II', 'III', 'IV'])->nullable();
            $table->enum('aap_grade', ['A', 'B', 'C'])->nullable();
            $table->enum('overall_risk_level', ['low', 'moderate', 'high', 'very_high'])->nullable();
            $table->text('notes')->nullable();
            $table->timestampTz('created_at')->useCurrent();

            $table->index('patient_id');
            $table->index('appointment_id');
        });

        Schema::create('perio_measurements', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('perio_chart_id')->constrained('perio_charts')->onDelete('cascade');
            $table->integer('tooth_number');
            $table->enum('surface', ['buccal', 'lingual']);
            $table->integer('position_1_probe')->nullable();
            $table->integer('position_2_probe')->nullable();
            $table->integer('position_3_probe')->nullable();
            $table->integer('position_1_recession')->nullable();
            $table->integer('position_2_recession')->nullable();
            $table->integer('position_3_recession')->nullable();
            $table->boolean('position_1_bleeding')->default(false);
            $table->boolean('position_2_bleeding')->default(false);
            $table->boolean('position_3_bleeding')->default(false);
            $table->boolean('position_1_suppuration')->default(false);
            $table->boolean('position_2_suppuration')->default(false);
            $table->boolean('position_3_suppuration')->default(false);
            $table->enum('furcation_class', ['none', 'I', 'II', 'III'])->default('none');
            $table->integer('mobility_grade')->default(0); // 0-3
            $table->boolean('plaque_present')->default(false);
            $table->boolean('calculus_present')->default(false);
            $table->timestampTz('created_at')->useCurrent();

            $table->index('perio_chart_id');
        });

        Schema::create('note_templates', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->constrained('tenants')->onDelete('cascade');
            $table->string('name', 200);
            $table->string('note_type', 50); // soap, progress, etc
            $table->text('template_content');
            $table->json('variables')->nullable(); // dynamic fields
            $table->boolean('is_global')->default(false);
            $table->foreignUuid('created_by')->constrained('users');
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestampsTz();

            $table->index('tenant_id');
        });

        Schema::create('clinical_notes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('patient_id')->constrained('patients')->onDelete('cascade');
            $table->foreignUuid('appointment_id')->nullable()->constrained('appointments');
            $table->foreignUuid('provider_id')->constrained('users');
            $table->enum('note_type', ['soap', 'progress', 'consult', 'referral', 'phone', 'general'])->default('soap');
            
            $table->text('subjective')->nullable();
            $table->text('objective')->nullable();
            $table->text('assessment')->nullable();
            $table->text('plan')->nullable();
            $table->text('full_note_text');
            
            $table->boolean('is_locked')->default(false);
            $table->timestampTz('locked_at')->nullable();
            $table->foreignUuid('locked_by')->nullable()->constrained('users');
            
            $table->foreignUuid('template_id')->nullable()->constrained('note_templates');
            
            $table->boolean('is_signed')->default(false);
            $table->timestampTz('signed_at')->nullable();
            $table->foreignUuid('co_signed_by')->nullable()->constrained('users');
            $table->timestampTz('co_signed_at')->nullable();
            
            $table->boolean('is_amended')->default(false);
            $table->text('amendment_notes')->nullable();
            $table->integer('version')->default(1);
            
            $table->timestampsTz();
            $table->softDeletesTz();

            $table->index('patient_id');
            $table->index('appointment_id');
            $table->index('provider_id');
        });

        Schema::create('vital_signs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('patient_id')->constrained('patients')->onDelete('cascade');
            $table->foreignUuid('appointment_id')->constrained('appointments')->onDelete('cascade');
            $table->integer('blood_pressure_systolic')->nullable();
            $table->integer('blood_pressure_diastolic')->nullable();
            $table->integer('pulse_rate')->nullable();
            $table->integer('respiratory_rate')->nullable();
            $table->decimal('temperature', 5, 2)->nullable();
            $table->integer('oxygen_saturation')->nullable();
            $table->decimal('weight_kg', 8, 2)->nullable();
            $table->decimal('height_cm', 8, 2)->nullable();
            $table->decimal('bmi', 5, 2)->nullable(); // computed
            $table->foreignUuid('recorded_by')->constrained('users');
            $table->timestampTz('recorded_at')->useCurrent();
            $table->text('notes')->nullable();
            $table->timestampTz('created_at')->useCurrent();

            $table->index('patient_id');
            $table->index('appointment_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vital_signs');
        Schema::dropIfExists('clinical_notes');
        Schema::dropIfExists('note_templates');
        Schema::dropIfExists('perio_measurements');
        Schema::dropIfExists('perio_charts');
        Schema::dropIfExists('implants');
        Schema::dropIfExists('restorations');
        Schema::dropIfExists('tooth_conditions');
        Schema::dropIfExists('dental_charts');
    }
};
