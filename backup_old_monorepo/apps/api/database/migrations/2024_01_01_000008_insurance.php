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
        Schema::create('cdt_codes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('code', 20)->unique();
            $table->text('description');
            $table->enum('category', [
                'diagnostic', 'preventive', 'restorative', 'endodontics', 
                'periodontics', 'prosthodontics', 'maxillofacial', 
                'orthodontics', 'adjunctive', 'other'
            ])->default('other');
            $table->boolean('is_active')->default(true);
            $table->timestampTz('created_at')->useCurrent();
        });

        Schema::create('insurance_carriers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->nullable()->constrained('tenants')->onDelete('cascade'); // null = global
            $table->string('name', 255);
            $table->json('address')->nullable();
            $table->string('phone', 50)->nullable();
            $table->string('fax', 50)->nullable();
            $table->string('website', 255)->nullable();
            $table->string('payer_id', 100)->nullable()->index(); // for EDI
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestampsTz();

            $table->index('tenant_id');
        });

        Schema::create('patient_insurance', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('patient_id')->constrained('patients')->onDelete('cascade');
            $table->foreignUuid('carrier_id')->constrained('insurance_carriers');
            $table->string('plan_name', 255)->nullable();
            $table->string('group_number', 100)->nullable();
            $table->string('subscriber_id', 100)->index();
            $table->string('subscriber_name', 255);
            $table->date('subscriber_dob');
            $table->enum('subscriber_relationship', ['self', 'spouse', 'child', 'other'])->default('self');
            $table->string('employer_name', 255)->nullable();
            $table->date('effective_date')->nullable();
            $table->date('termination_date')->nullable();
            $table->boolean('is_primary')->default(true);
            $table->integer('sequence')->default(1); // 1=primary, 2=secondary...
            
            $table->decimal('annual_maximum', 15, 2)->nullable();
            $table->decimal('deductible_individual', 15, 2)->nullable();
            $table->decimal('deductible_family', 15, 2)->nullable();
            $table->decimal('deductible_met', 15, 2)->default(0);
            $table->decimal('benefits_used_ytd', 15, 2)->default(0);
            $table->integer('benefit_year_start')->default(1); // month 1-12
            
            $table->json('waiting_periods')->nullable();
            $table->json('covered_percentages')->nullable(); // {preventive, basic...}
            $table->boolean('missing_tooth_clause')->default(false);
            $table->string('coordination_of_benefits_type', 100)->nullable();
            $table->decimal('pre_auth_required_above', 15, 2)->nullable();
            
            $table->string('insurance_card_front_url', 500)->nullable();
            $table->string('insurance_card_back_url', 500)->nullable();
            
            $table->timestampTz('verified_at')->nullable();
            $table->foreignUuid('verified_by')->nullable()->constrained('users');
            $table->json('eligibility_response')->nullable();
            
            $table->timestampsTz();
            $table->softDeletesTz();

            $table->index('patient_id');
            $table->index('carrier_id');
        });

        Schema::create('fee_schedules', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->constrained('tenants')->onDelete('cascade');
            $table->foreignUuid('carrier_id')->nullable()->constrained('insurance_carriers')->onDelete('set null');
            $table->string('name', 200);
            $table->enum('type', ['ucr_standard', 'insurance_contracted', 'medicaid', 'custom'])->default('ucr_standard');
            $table->date('effective_date')->nullable();
            $table->date('expiry_date')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestampsTz();

            $table->index('tenant_id');
        });

        Schema::create('fee_schedule_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('fee_schedule_id')->constrained('fee_schedules')->onDelete('cascade');
            $table->string('cdt_code', 20); // Normalized code
            $table->text('description')->nullable();
            $table->decimal('fee', 15, 2)->default(0);
            $table->timestampsTz();

            $table->index('fee_schedule_id');
            $table->index('cdt_code');
        });

        Schema::create('claims', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->constrained('tenants')->onDelete('cascade');
            $table->string('claim_number', 100)->index();
            $table->foreignUuid('patient_id')->constrained('patients');
            $table->foreignUuid('insurance_id')->constrained('patient_insurance');
            $table->foreignUuid('appointment_id')->nullable()->constrained('appointments');
            $table->foreignUuid('rendering_provider_id')->constrained('users');
            $table->foreignUuid('billing_provider_id')->constrained('users');
            $table->foreignUuid('location_id')->constrained('locations');
            
            $table->enum('claim_type', ['primary', 'secondary', 'tertiary'])->default('primary');
            $table->enum('status', [
                'draft', 'submitted', 'pending', 'additional_info_required', 
                'partial_payment', 'paid', 'denied', 'appealed', 'void', 'written_off'
            ])->default('draft');
            
            $table->decimal('total_billed', 15, 2)->default(0);
            $table->decimal('total_paid', 15, 2)->default(0);
            $table->decimal('patient_portion', 15, 2)->default(0);
            $table->decimal('write_off', 15, 2)->default(0);
            
            $table->timestampTz('submitted_at')->nullable();
            $table->timestampTz('paid_at')->nullable();
            $table->string('check_number', 100)->nullable();
            $table->date('check_date')->nullable();
            
            $table->uuid('era_id')->nullable(); // FK will be defined later
            $table->string('clearinghouse_claim_id', 255)->nullable()->index();
            $table->json('rejection_codes')->nullable();
            $table->text('denial_reason')->nullable();
            $table->text('appeal_notes')->nullable();
            $table->string('pre_auth_number', 100)->nullable();
            
            $table->timestampsTz();

            $table->index('tenant_id');
            $table->index('patient_id');
            $table->index('status');
        });

        Schema::create('claim_line_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('claim_id')->constrained('claims')->onDelete('cascade');
            $table->foreignUuid('treatment_plan_procedure_id')->nullable()->constrained('treatment_plan_procedures');
            $table->integer('tooth_number')->nullable();
            $table->json('surfaces')->nullable();
            $table->string('cdt_code', 20);
            $table->text('description')->nullable();
            $table->decimal('fee_billed', 15, 2)->default(0);
            $table->decimal('fee_allowed', 15, 2)->default(0);
            $table->decimal('insurance_paid', 15, 2)->default(0);
            $table->decimal('patient_portion', 15, 2)->default(0);
            $table->decimal('adjustment', 15, 2)->default(0);
            $table->string('adjustment_type', 100)->nullable();
            $table->enum('status', ['included', 'paid', 'denied', 'adjusted'])->default('included');
            $table->text('denial_reason')->nullable();
            $table->timestampsTz();

            $table->index('claim_id');
        });

        Schema::create('claim_attachments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('claim_id')->constrained('claims')->onDelete('cascade');
            $table->enum('attachment_type', ['xray', 'photo', 'perio_chart', 'narrative', 'other'])->default('other');
            $table->string('file_url', 500);
            $table->string('file_name', 255);
            $table->bigInteger('file_size_bytes')->nullable();
            $table->foreignUuid('created_by')->constrained('users');
            $table->timestampTz('created_at')->useCurrent();
        });

        Schema::create('claim_narratives', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->constrained('tenants')->onDelete('cascade');
            $table->string('cdt_code', 20);
            $table->string('title', 255);
            $table->text('narrative_text');
            $table->boolean('is_template')->default(false);
            $table->foreignUuid('created_by')->constrained('users');
            $table->timestampsTz();

            $table->index('tenant_id');
        });

        Schema::create('eras', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->constrained('tenants')->onDelete('cascade');
            $table->string('clearinghouse_id', 100)->nullable();
            $table->string('file_name', 255)->nullable();
            $table->timestampTz('received_at')->useCurrent();
            $table->string('payer_name', 255)->nullable();
            $table->string('payer_id', 100)->nullable();
            $table->string('check_number', 100)->nullable();
            $table->date('check_date')->nullable();
            $table->decimal('total_payment', 15, 2)->default(0);
            $table->boolean('is_posted')->default(false);
            $table->timestampTz('posted_at')->nullable();
            $table->foreignUuid('posted_by')->nullable()->constrained('users');
            $table->json('raw_data')->nullable();
            $table->timestampsTz();

            $table->index('tenant_id');
            $table->index('received_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('eras');
        Schema::dropIfExists('claim_narratives');
        Schema::dropIfExists('claim_attachments');
        Schema::dropIfExists('claim_line_items');
        Schema::dropIfExists('claims');
        Schema::dropIfExists('fee_schedule_items');
        Schema::dropIfExists('fee_schedules');
        Schema::dropIfExists('patient_insurance');
        Schema::dropIfExists('insurance_carriers');
        Schema::dropIfExists('cdt_codes');
    }
};
