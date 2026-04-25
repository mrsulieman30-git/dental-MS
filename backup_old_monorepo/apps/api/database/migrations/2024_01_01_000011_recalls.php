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
        Schema::create('recall_types', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->constrained('tenants')->onDelete('cascade');
            $table->string('name', 100);
            $table->text('description')->nullable();
            $table->integer('default_interval_months')->default(6);
            $table->string('associated_cdt_code', 20)->nullable();
            $table->string('color', 7)->default('#0ea5e9');
            $table->boolean('is_active')->default(true);
            $table->boolean('send_reminder')->default(true);
            $table->json('reminder_days_before')->nullable(); // [14, 7, 1]
            $table->timestampsTz();

            $table->index('tenant_id');
        });

        Schema::create('patient_recalls', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('patient_id')->constrained('patients')->onDelete('cascade');
            $table->foreignUuid('recall_type_id')->constrained('recall_types');
            $table->foreignUuid('provider_id')->nullable()->constrained('users');
            $table->date('due_date');
            $table->date('last_completed_date')->nullable();
            $table->foreignUuid('last_completed_appointment_id')->nullable()->constrained('appointments');
            $table->enum('status', ['due', 'overdue', 'scheduled', 'completed', 'declined', 'inactive'])->default('due');
            $table->foreignUuid('appointment_id')->nullable()->constrained('appointments');
            $table->text('notes')->nullable();
            $table->timestampsTz();

            $table->index('patient_id');
            $table->index('due_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('patient_recalls');
        Schema::dropIfExists('recall_types');
    }
};
