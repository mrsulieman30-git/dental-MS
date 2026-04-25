<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prescriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained('patients')->onDelete('cascade');
            $table->foreignId('prescriber_id')->constrained('users')->onDelete('cascade');
            $table->string('drug_name');
            $table->string('generic_name')->nullable();
            $table->string('ndc_code')->nullable();
            $table->string('dosage');
            $table->string('dosage_unit');
            $table->integer('quantity');
            $table->tinyInteger('refills')->default(0);
            $table->string('frequency');
            $table->string('route');
            $table->text('sig'); // patient directions
            $table->boolean('is_controlled_substance')->default(false);
            $table->enum('dea_schedule', ['II', 'III', 'IV', 'V'])->nullable();
            $table->string('diagnosis_code')->nullable(); // ICD-10
            $table->string('pharmacy_name')->nullable();
            $table->string('pharmacy_ncpdp_id')->nullable();
            $table->json('pharmacy_address')->nullable();
            $table->enum('status', ['draft', 'sent', 'dispensed', 'cancelled', 'expired'])->default('draft');
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('dispensed_at')->nullable();
            $table->string('external_rx_id')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prescriptions');
    }
};
