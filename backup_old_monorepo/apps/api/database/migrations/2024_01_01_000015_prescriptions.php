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
        Schema::create('prescriptions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('patient_id')->constrained('patients')->onDelete('cascade');
            $table->foreignUuid('prescriber_id')->constrained('users');
            
            $table->string('drug_name', 255);
            $table->string('generic_name', 255)->nullable();
            $table->string('ndc_code', 20)->nullable();
            $table->string('dosage', 100)->nullable();
            $table->string('dosage_unit', 50)->nullable();
            $table->decimal('quantity', 10, 2)->default(0);
            $table->integer('refills')->default(0);
            $table->string('frequency', 100)->nullable();
            $table->string('route', 50)->nullable();
            $table->text('sig')->nullable(); // patient directions
            
            $table->boolean('is_controlled_substance')->default(false);
            $table->enum('dea_schedule', ['II', 'III', 'IV', 'V'])->nullable();
            $table->string('diagnosis_code', 20)->nullable(); // ICD-10
            
            $table->string('pharmacy_name', 255)->nullable();
            $table->string('pharmacy_ncpdp_id', 50)->nullable();
            $table->json('pharmacy_address')->nullable();
            
            $table->enum('status', ['draft', 'sent', 'dispensed', 'cancelled', 'expired'])->default('draft');
            $table->timestampTz('sent_at')->nullable();
            $table->timestampTz('dispensed_at')->nullable();
            $table->string('external_rx_id', 255)->nullable()->index();
            $table->text('notes')->nullable();
            
            $table->timestampsTz();
            $table->softDeletesTz();

            $table->index('patient_id');
            $table->index('prescriber_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prescriptions');
    }
};
