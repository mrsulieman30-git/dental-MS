<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('report_definitions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
            $table->string('name');
            $table->string('report_type');
            $table->json('query_config');
            $table->json('filters_config')->nullable();
            $table->json('columns_config')->nullable();
            $table->boolean('is_system')->default(false);
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });

        Schema::create('scheduled_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('report_definition_id')->constrained('report_definitions')->onDelete('cascade');
            $table->json('recipient_user_ids'); // array of user IDs
            $table->enum('frequency', ['daily', 'weekly', 'monthly']);
            $table->tinyInteger('day_of_week')->nullable(); // 0=Sun, 6=Sat
            $table->tinyInteger('day_of_month')->nullable();
            $table->time('time_of_day');
            $table->enum('format', ['pdf', 'csv', 'excel'])->default('pdf');
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_sent_at')->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });

        Schema::create('practice_goals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
            $table->foreignId('location_id')->nullable()->constrained('locations')->onDelete('set null');
            $table->enum('goal_type', ['production', 'collection', 'new_patients', 'case_acceptance', 'recall_rate', 'other']);
            $table->enum('period', ['monthly', 'quarterly', 'annual'])->default('monthly');
            $table->decimal('target_value', 10, 2);
            $table->date('period_start_date');
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('practice_goals');
        Schema::dropIfExists('scheduled_reports');
        Schema::dropIfExists('report_definitions');
    }
};
