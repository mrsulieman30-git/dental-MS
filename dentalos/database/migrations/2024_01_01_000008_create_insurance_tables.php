<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('insurance_carriers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->nullable()->constrained('tenants')->onDelete('cascade');
            $table->string('name');
            $table->json('address')->nullable();
            $table->string('phone')->nullable();
            $table->string('fax')->nullable();
            $table->string('website')->nullable();
            $table->string('payer_id')->nullable(); // EDI payer ID
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('cdt_codes', function (Blueprint $table) {
            $table->id();
            $table->string('code', 6)->unique();
            $table->text('description');
            $table->string('short_description');
            $table->enum('category', ['diagnostic', 'preventive', 'restorative', 'endodontics', 'periodontics', 'prosthodontics', 'maxillofacial', 'orthodontics', 'adjunctive', 'other']);
            $table->boolean('is_active')->default(true);
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('patient_insurance', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained('patients')->onDelete('cascade');
            $table->foreignId('carrier_id')->constrained('insurance_carriers')->onDelete('cascade');
            $table->string('plan_name');
            $table->string('group_number')->nullable();
            $table->string('subscriber_id');
            $table->string('subscriber_name');
            $table->date('subscriber_dob')->nullable();
            $table->enum('subscriber_relationship', ['self', 'spouse', 'child', 'other'])->default('self');
            $table->string('employer_name')->nullable();
            $table->date('effective_date')->nullable();
            $table->date('termination_date')->nullable();
            $table->boolean('is_primary')->default(true);
            $table->tinyInteger('sequence')->default(1); // 1=primary, 2=secondary, 3=tertiary
            $table->decimal('annual_maximum', 10, 2)->nullable();
            $table->decimal('deductible_individual', 10, 2)->nullable();
            $table->decimal('deductible_family', 10, 2)->nullable();
            $table->decimal('deductible_met', 10, 2)->default(0);
            $table->decimal('benefits_used_ytd', 10, 2)->default(0);
            $table->tinyInteger('benefit_year_start')->default(1); // month 1-12
            $table->json('waiting_periods')->nullable();
            $table->json('covered_percentages')->nullable(); // {preventive,basic,major,ortho,implant}
            $table->boolean('missing_tooth_clause')->default(false);
            $table->string('coordination_of_benefits_type')->nullable();
            $table->decimal('pre_auth_required_above', 10, 2)->nullable();
            $table->string('insurance_card_front_path')->nullable();
            $table->string('insurance_card_back_path')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->onDelete('set null');
            $table->json('eligibility_response')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('fee_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
            $table->foreignId('carrier_id')->nullable()->constrained('insurance_carriers')->onDelete('set null');
            $table->string('name');
            $table->enum('type', ['ucr', 'insurance_contracted', 'medicaid', 'custom'])->default('ucr');
            $table->date('effective_date')->nullable();
            $table->date('expiry_date')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('fee_schedule_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fee_schedule_id')->constrained('fee_schedules')->onDelete('cascade');
            $table->foreignId('cdt_code_id')->constrained('cdt_codes')->onDelete('cascade');
            $table->decimal('fee', 10, 2);
            $table->timestamps();

            $table->unique(['fee_schedule_id', 'cdt_code_id']);
        });

        Schema::create('claims', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
            $table->string('claim_number');
            $table->foreignId('patient_id')->constrained('patients')->onDelete('cascade');
            $table->foreignId('insurance_id')->constrained('patient_insurance')->onDelete('cascade');
            $table->foreignId('appointment_id')->nullable()->constrained('appointments')->onDelete('set null');
            $table->foreignId('rendering_provider_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('billing_provider_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('location_id')->constrained('locations')->onDelete('cascade');
            $table->enum('claim_type', ['primary', 'secondary', 'tertiary'])->default('primary');
            $table->enum('status', ['draft', 'submitted', 'pending', 'additional_info_required', 'partial_payment', 'paid', 'denied', 'appealed', 'void', 'written_off'])->default('draft');
            $table->decimal('total_billed', 10, 2)->default(0);
            $table->decimal('total_paid', 10, 2)->default(0);
            $table->decimal('patient_portion', 10, 2)->default(0);
            $table->decimal('write_off', 10, 2)->default(0);
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->string('check_number')->nullable();
            $table->date('check_date')->nullable();
            $table->bigInteger('era_id')->nullable();
            $table->string('clearinghouse_claim_id')->nullable();
            $table->json('rejection_codes')->nullable();
            $table->text('denial_reason')->nullable();
            $table->text('appeal_notes')->nullable();
            $table->string('pre_auth_number')->nullable();
            $table->timestamps();
        });

        Schema::create('claim_line_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('claim_id')->constrained('claims')->onDelete('cascade');
            $table->bigInteger('treatment_plan_procedure_id')->nullable();
            $table->tinyInteger('tooth_number')->nullable();
            $table->json('surfaces')->nullable();
            $table->foreignId('cdt_code_id')->constrained('cdt_codes')->onDelete('cascade');
            $table->string('description');
            $table->decimal('fee_billed', 10, 2);
            $table->decimal('fee_allowed', 10, 2)->default(0);
            $table->decimal('insurance_paid', 10, 2)->default(0);
            $table->decimal('patient_portion', 10, 2)->default(0);
            $table->decimal('adjustment', 10, 2)->default(0);
            $table->string('adjustment_type')->nullable();
            $table->enum('status', ['included', 'paid', 'denied', 'adjusted'])->default('included');
            $table->text('denial_reason')->nullable();
            $table->timestamps();
        });

        Schema::create('claim_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('claim_id')->constrained('claims')->onDelete('cascade');
            $table->enum('attachment_type', ['xray', 'photo', 'perio_chart', 'narrative', 'other']);
            $table->string('file_path');
            $table->string('file_name');
            $table->integer('file_size_bytes');
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });

        Schema::create('claim_narratives', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
            $table->foreignId('cdt_code_id')->nullable()->constrained('cdt_codes')->onDelete('set null');
            $table->string('title');
            $table->text('narrative_text');
            $table->boolean('is_template')->default(true);
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });

        Schema::create('eras', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
            $table->string('clearinghouse_id')->nullable();
            $table->string('file_name')->nullable();
            $table->timestamp('received_at');
            $table->string('payer_name')->nullable();
            $table->string('payer_id')->nullable();
            $table->string('check_number')->nullable();
            $table->date('check_date')->nullable();
            $table->decimal('total_payment', 10, 2);
            $table->boolean('is_posted')->default(false);
            $table->timestamp('posted_at')->nullable();
            $table->foreignId('posted_by')->nullable()->constrained('users')->onDelete('set null');
            $table->json('raw_data')->nullable();
            $table->timestamps();
        });
    }

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
        Schema::dropIfExists('cdt_codes');
        Schema::dropIfExists('insurance_carriers');
    }
};
