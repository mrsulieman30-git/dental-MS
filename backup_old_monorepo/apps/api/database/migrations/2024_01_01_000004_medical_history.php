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
        Schema::create('medical_histories', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('patient_id')->constrained('patients')->onDelete('cascade');
            $table->integer('version')->default(1);
            $table->foreignUuid('reviewed_by')->constrained('users');
            $table->timestampTz('reviewed_at')->nullable();
            $table->boolean('signed_by_patient')->default(false);
            $table->timestampTz('signed_at')->nullable();
            $table->enum('asa_classification', ['I', 'II', 'III', 'IV', 'V', 'VI'])->nullable();
            $table->boolean('is_current')->default(true);
            $table->text('notes')->nullable();
            $table->timestampTz('created_at')->useCurrent();

            $table->index('patient_id');
        });

        Schema::create('medical_conditions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('medical_history_id')->constrained('medical_histories')->onDelete('cascade');
            $table->string('condition_name', 255);
            $table->string('icd10_code', 20)->nullable();
            $table->boolean('is_active')->default(true);
            $table->date('diagnosed_date')->nullable();
            $table->string('treating_physician', 255)->nullable();
            $table->text('notes')->nullable();
            $table->timestampTz('created_at')->useCurrent();

            $table->index('medical_history_id');
        });

        Schema::create('medications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('patient_id')->constrained('patients')->onDelete('cascade');
            $table->string('drug_name', 255);
            $table->string('generic_name', 255)->nullable();
            $table->string('dosage', 100)->nullable();
            $table->string('unit', 50)->nullable();
            $table->string('frequency', 100)->nullable();
            $table->string('route', 50)->nullable();
            $table->string('prescribing_doctor', 255)->nullable();
            $table->string('prescribing_doctor_phone', 50)->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->boolean('is_active')->default(true);
            $table->text('reason')->nullable();
            $table->text('notes')->nullable();
            $table->timestampsTz();

            $table->index('patient_id');
        });

        Schema::create('allergies', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('patient_id')->constrained('patients')->onDelete('cascade');
            $table->string('allergen', 255);
            $table->enum('allergy_type', ['drug', 'food', 'latex', 'metal', 'environmental', 'other'])->default('other');
            $table->string('reaction_type', 255)->nullable();
            $table->enum('severity', ['mild', 'moderate', 'severe', 'unknown'])->default('unknown');
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestampsTz();

            $table->index('patient_id');
        });

        Schema::create('medical_history_responses', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('medical_history_id')->constrained('medical_histories')->onDelete('cascade');
            $table->string('question_key', 100); // references form question
            $table->json('response'); // yes/no/detail answers
            $table->timestampTz('created_at')->useCurrent();

            $table->index('medical_history_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medical_history_responses');
        Schema::dropIfExists('allergies');
        Schema::dropIfExists('medications');
        Schema::dropIfExists('medical_conditions');
        Schema::dropIfExists('medical_histories');
    }
};
