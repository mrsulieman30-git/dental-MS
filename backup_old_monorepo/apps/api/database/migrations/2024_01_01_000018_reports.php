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
        Schema::create('report_definitions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->constrained('tenants')->onDelete('cascade');
            $table->string('name', 200);
            $table->string('report_type', 100);
            $table->json('query_config');
            $table->json('filters_config')->nullable();
            $table->json('columns_config')->nullable();
            $table->boolean('is_system')->default(false);
            $table->foreignUuid('created_by')->nullable()->constrained('users');
            $table->timestampsTz();

            $table->index('tenant_id');
        });

        Schema::create('scheduled_reports', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('report_definition_id')->constrained('report_definitions')->onDelete('cascade');
            $table->json('recipient_user_ids'); // array of UUIDs
            $table->enum('frequency', ['daily', 'weekly', 'monthly'])->default('weekly');
            $table->integer('day_of_week')->nullable(); // 1-7
            $table->integer('day_of_month')->nullable(); // 1-31
            $table->time('time_of_day')->default('08:00:00');
            $table->enum('format', ['pdf', 'csv', 'excel'])->default('pdf');
            $table->boolean('is_active')->default(true);
            $table->timestampTz('last_sent_at')->nullable();
            $table->foreignUuid('created_by')->constrained('users');
            $table->timestampsTz();

            $table->index('report_definition_id');
        });

        Schema::create('practice_goals', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->constrained('tenants')->onDelete('cascade');
            $table->foreignUuid('location_id')->nullable()->constrained('locations');
            $table->enum('goal_type', ['production', 'collection', 'new_patients', 'case_acceptance', 'recall_rate', 'other'])->default('production');
            $table->enum('period', ['monthly', 'quarterly', 'annual'])->default('monthly');
            $table->decimal('target_value', 15, 2);
            $table->date('period_start_date');
            $table->foreignUuid('created_by')->constrained('users');
            $table->timestampsTz();

            $table->index('tenant_id');
            $table->index('period_start_date');
        });

        // Add the cross-migration foreign key for claims -> eras
        Schema::table('claims', function (Blueprint $table) {
            $table->foreign('era_id')->references('id')->on('eras');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('claims', function (Blueprint $table) {
            $table->dropForeign(['era_id']);
        });
        Schema::dropIfExists('practice_goals');
        Schema::dropIfExists('scheduled_reports');
        Schema::dropIfExists('report_definitions');
    }
};
