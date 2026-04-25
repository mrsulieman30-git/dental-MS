<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('treatment_plans', function (Blueprint $table) {
            $table->string('signature_image_path')->nullable()->after('patient_signed_at');
            $table->json('phase_names')->nullable()->after('signature_image_path');
            $table->unsignedInteger('alternative_group')->nullable()->after('phase_names');
            $table->string('signer_ip')->nullable()->after('signature_image_path');
            $table->string('signer_name')->nullable()->after('signer_ip');
        });

        Schema::table('treatment_plan_procedures', function (Blueprint $table) {
            $table->timestamp('declined_at')->nullable()->after('notes');
            $table->text('declined_reason')->nullable()->after('declined_at');
        });

        Schema::table('patient_insurance', function (Blueprint $table) {
            $table->json('eligibility_history')->nullable()->after('eligibility_response');
        });

        Schema::table('insurance_carriers', function (Blueprint $table) {
            $table->string('logo_path')->nullable()->after('notes');
            $table->string('email')->nullable()->after('fax');
        });

        Schema::create('pre_authorizations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
            $table->foreignId('patient_id')->constrained('patients')->onDelete('cascade');
            $table->foreignId('insurance_id')->constrained('patient_insurance')->onDelete('cascade');
            $table->foreignId('cdt_code_id')->nullable()->constrained('cdt_codes')->onDelete('set null');
            $table->string('procedure_description');
            $table->tinyInteger('tooth_number')->nullable();
            $table->enum('status', ['pending', 'approved', 'denied', 'expired'])->default('pending');
            $table->string('auth_number')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('response_at')->nullable();
            $table->date('expiry_date')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pre_authorizations');

        Schema::table('insurance_carriers', function (Blueprint $table) {
            $table->dropColumn(['logo_path', 'email']);
        });

        Schema::table('patient_insurance', function (Blueprint $table) {
            $table->dropColumn('eligibility_history');
        });

        Schema::table('treatment_plan_procedures', function (Blueprint $table) {
            $table->dropColumn(['declined_at', 'declined_reason']);
        });

        Schema::table('treatment_plans', function (Blueprint $table) {
            $table->dropColumn(['signature_image_path', 'phase_names', 'alternative_group', 'signer_ip', 'signer_name']);
        });
    }
};
