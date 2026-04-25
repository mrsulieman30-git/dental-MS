<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dental_charts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->unique()->constrained('patients')->onDelete('cascade');
            $table->enum('notation_system', ['universal', 'fdi', 'palmer'])->default('universal');
            $table->timestamps();
        });

        Schema::create('tooth_conditions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dental_chart_id')->constrained('dental_charts')->onDelete('cascade');
            $table->foreignId('appointment_id')->nullable()->constrained('appointments')->onDelete('set null');
            $table->unsignedTinyInteger('tooth_number');
            $table->json('surfaces')->nullable(); // array of M,D,O,B,L,I,F
            $table->enum('condition_type', ['caries', 'fracture', 'wear', 'sensitivity', 'mobility', 'peri_implantitis', 'perio', 'other']);
            $table->enum('severity', ['initial', 'moderate', 'severe', 'watch'])->default('watch');
            $table->enum('status', ['existing', 'proposed', 'in_progress', 'completed', 'declined', 'referred', 'monitored'])->default('existing');
            $table->string('cdt_code')->nullable();
            $table->bigInteger('procedure_id')->nullable();
            $table->text('notes')->nullable();
            $table->date('diagnosed_date')->nullable();
            $table->date('treated_date')->nullable();
            $table->foreignId('diagnosed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('treated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('restorations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dental_chart_id')->constrained('dental_charts')->onDelete('cascade');
            $table->foreignId('appointment_id')->nullable()->constrained('appointments')->onDelete('set null');
            $table->unsignedTinyInteger('tooth_number');
            $table->json('surfaces')->nullable();
            $table->enum('restoration_type', ['filling', 'crown', 'bridge', 'implant', 'veneer', 'onlay', 'inlay', 'denture_partial', 'denture_full', 'sealant', 'rct', 'buildup', 'post_core', 'other']);
            $table->enum('material', ['amalgam', 'composite', 'gold', 'porcelain', 'zirconia', 'pfm', 'acrylic', 'other'])->nullable();
            $table->string('shade')->nullable();
            $table->enum('tooth_position', ['single', 'abutment', 'pontic'])->nullable();
            $table->json('bridge_teeth')->nullable();
            $table->bigInteger('lab_case_id')->nullable();
            $table->date('placement_date')->nullable();
            $table->foreignId('placed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->text('notes')->nullable();
            $table->enum('status', ['existing', 'new', 'needs_replacement', 'failed'])->default('existing');
            $table->timestamps();
        });

        Schema::create('implants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dental_chart_id')->constrained('dental_charts')->onDelete('cascade');
            $table->tinyInteger('tooth_number');
            $table->string('implant_system')->nullable();
            $table->string('implant_brand')->nullable();
            $table->decimal('fixture_diameter', 4, 2)->nullable();
            $table->decimal('fixture_length', 4, 2)->nullable();
            $table->date('placement_date')->nullable();
            $table->foreignId('placed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('placement_location_id')->nullable()->constrained('locations')->onDelete('set null');
            $table->date('restoration_date')->nullable();
            $table->string('crown_material')->nullable();
            $table->string('abutment_type')->nullable();
            $table->decimal('torque_value', 5, 2)->nullable();
            $table->string('bone_graft_material')->nullable();
            $table->boolean('membrane_used')->nullable();
            $table->string('lot_number')->nullable();
            $table->string('serial_number')->nullable();
            $table->enum('implant_status', ['active', 'healing', 'failed', 'removed'])->default('healing');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('perio_charts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained('patients')->onDelete('cascade');
            $table->foreignId('appointment_id')->constrained('appointments')->onDelete('cascade');
            $table->foreignId('provider_id')->constrained('users')->onDelete('cascade');
            $table->date('chart_date');
            $table->enum('aap_stage', ['I', 'II', 'III', 'IV'])->nullable();
            $table->enum('aap_grade', ['A', 'B', 'C'])->nullable();
            $table->enum('overall_risk_level', ['low', 'moderate', 'high', 'very_high'])->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('perio_measurements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('perio_chart_id')->constrained('perio_charts')->onDelete('cascade');
            $table->tinyInteger('tooth_number');
            $table->enum('surface', ['buccal', 'lingual']);
            $table->tinyInteger('pos1_probe')->nullable();
            $table->tinyInteger('pos2_probe')->nullable();
            $table->tinyInteger('pos3_probe')->nullable();
            $table->tinyInteger('pos1_recession')->default(0);
            $table->tinyInteger('pos2_recession')->default(0);
            $table->tinyInteger('pos3_recession')->default(0);
            $table->boolean('pos1_bleeding')->default(false);
            $table->boolean('pos2_bleeding')->default(false);
            $table->boolean('pos3_bleeding')->default(false);
            $table->boolean('pos1_suppuration')->default(false);
            $table->boolean('pos2_suppuration')->default(false);
            $table->boolean('pos3_suppuration')->default(false);
            $table->enum('furcation_class', ['none', 'I', 'II', 'III'])->default('none');
            $table->tinyInteger('mobility_grade')->default(0);
            $table->boolean('plaque_present')->default(false);
            $table->boolean('calculus_present')->default(false);
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('clinical_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained('patients')->onDelete('cascade');
            $table->foreignId('appointment_id')->nullable()->constrained('appointments')->onDelete('set null');
            $table->foreignId('provider_id')->constrained('users')->onDelete('cascade');
            $table->enum('note_type', ['soap', 'progress', 'consult', 'referral', 'phone', 'general'])->default('soap');
            $table->text('subjective')->nullable();
            $table->text('objective')->nullable();
            $table->text('assessment')->nullable();
            $table->text('plan')->nullable();
            $table->longText('full_note_text')->nullable();
            $table->boolean('is_locked')->default(false);
            $table->timestamp('locked_at')->nullable();
            $table->foreignId('locked_by')->nullable()->constrained('users')->onDelete('set null');
            $table->bigInteger('template_id')->nullable();
            $table->boolean('is_signed')->default(false);
            $table->timestamp('signed_at')->nullable();
            $table->foreignId('co_signed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('co_signed_at')->nullable();
            $table->boolean('is_amended')->default(false);
            $table->text('amendment_notes')->nullable();
            $table->integer('version')->default(1);
            $table->timestamps();
        });

        Schema::create('note_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
            $table->string('name');
            $table->enum('note_type', ['soap', 'progress', 'consult', 'referral', 'phone', 'general']);
            $table->longText('template_content');
            $table->json('variables')->nullable();
            $table->boolean('is_global')->default(false);
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('vital_signs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained('patients')->onDelete('cascade');
            $table->foreignId('appointment_id')->constrained('appointments')->onDelete('cascade');
            $table->tinyInteger('blood_pressure_systolic')->nullable();
            $table->tinyInteger('blood_pressure_diastolic')->nullable();
            $table->tinyInteger('pulse_rate')->nullable();
            $table->tinyInteger('respiratory_rate')->nullable();
            $table->decimal('temperature', 4, 1)->nullable();
            $table->tinyInteger('oxygen_saturation')->nullable();
            $table->decimal('weight_kg', 5, 2)->nullable();
            $table->decimal('height_cm', 5, 2)->nullable();
            $table->decimal('bmi', 4, 2)->nullable();
            $table->foreignId('recorded_by')->constrained('users')->onDelete('cascade');
            $table->datetime('recorded_at');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vital_signs');
        Schema::dropIfExists('note_templates');
        Schema::dropIfExists('clinical_notes');
        Schema::dropIfExists('perio_measurements');
        Schema::dropIfExists('perio_charts');
        Schema::dropIfExists('implants');
        Schema::dropIfExists('restorations');
        Schema::dropIfExists('tooth_conditions');
        Schema::dropIfExists('dental_charts');
    }
};
