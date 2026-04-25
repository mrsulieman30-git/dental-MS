<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('medical_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained('patients')->onDelete('cascade');
            $table->integer('version')->default(1);
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('reviewed_at')->nullable();
            $table->boolean('signed_by_patient')->default(false);
            $table->timestamp('signed_at')->nullable();
            $table->enum('asa_classification', ['I', 'II', 'III', 'IV', 'V', 'VI'])->nullable();
            $table->boolean('is_current')->default(true);
            $table->text('notes')->nullable();
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('medical_conditions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('medical_history_id')->constrained('medical_histories')->onDelete('cascade');
            $table->string('condition_name');
            $table->string('icd10_code')->nullable();
            $table->boolean('is_active')->default(true);
            $table->date('diagnosed_date')->nullable();
            $table->string('treating_physician')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('patient_medications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained('patients')->onDelete('cascade');
            $table->string('drug_name');
            $table->string('generic_name')->nullable();
            $table->string('dosage');
            $table->string('unit'); // mg, ml, mcg, units
            $table->string('frequency');
            $table->string('route'); // oral, topical, injection, other
            $table->string('prescribing_doctor')->nullable();
            $table->string('prescribing_doctor_phone')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->boolean('is_active')->default(true);
            $table->string('reason')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('patient_allergies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained('patients')->onDelete('cascade');
            $table->string('allergen');
            $table->enum('allergy_type', ['drug', 'food', 'latex', 'metal', 'environmental', 'other']);
            $table->string('reaction_type')->nullable();
            $table->enum('severity', ['mild', 'moderate', 'severe', 'unknown'])->default('unknown');
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('medical_history_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('medical_history_id')->constrained('medical_histories')->onDelete('cascade');
            $table->string('question_key');
            $table->json('response');
            $table->timestamp('created_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medical_history_responses');
        Schema::dropIfExists('patient_allergies');
        Schema::dropIfExists('patient_medications');
        Schema::dropIfExists('medical_conditions');
        Schema::dropIfExists('medical_histories');
    }
};
