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
        Schema::create('adjustment_types', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->constrained('tenants')->onDelete('cascade');
            $table->string('name', 200);
            $table->string('code', 20)->nullable();
            $table->boolean('is_debit')->default(false);
            $table->boolean('affects_production')->default(true);
            $table->boolean('affects_collections')->default(true);
            $table->boolean('requires_approval')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestampsTz();

            $table->index('tenant_id');
        });

        Schema::create('payment_plans', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('patient_id')->constrained('patients')->onDelete('cascade');
            $table->decimal('total_amount', 15, 2);
            $table->decimal('down_payment', 15, 2)->default(0);
            $table->decimal('installment_amount', 15, 2);
            $table->enum('frequency', ['weekly', 'biweekly', 'monthly'])->default('monthly');
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->integer('number_of_payments');
            $table->integer('payments_made')->default(0);
            $table->decimal('remaining_balance', 15, 2);
            $table->enum('status', ['active', 'completed', 'defaulted', 'cancelled'])->default('active');
            $table->boolean('auto_debit_enabled')->default(false);
            $table->string('auto_debit_card_token', 255)->nullable();
            $table->integer('auto_debit_day_of_month')->nullable();
            $table->enum('financing_provider', [
                'internal', 'carecredit', 'sunbit', 'cherry', 'lendingclub', 'other'
            ])->default('internal');
            $table->string('financing_account_number', 100)->nullable();
            $table->text('notes')->nullable();
            $table->foreignUuid('created_by')->constrained('users');
            $table->timestampsTz();

            $table->index('patient_id');
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('patient_id')->constrained('patients')->onDelete('cascade');
            $table->foreignUuid('location_id')->constrained('locations');
            $table->date('payment_date');
            $table->decimal('amount', 15, 2);
            $table->enum('payment_method', [
                'cash', 'check', 'credit_card', 'debit_card', 'ach', 
                'hsa_fsa', 'financing', 'other'
            ])->default('credit_card');
            $table->string('card_last4', 4)->nullable();
            $table->string('card_brand', 50)->nullable();
            $table->string('check_number', 100)->nullable();
            $table->string('transaction_id', 255)->nullable()->index();
            $table->json('processor_response')->nullable();
            $table->foreignUuid('payment_plan_id')->nullable()->constrained('payment_plans');
            $table->boolean('is_refunded')->default(false);
            $table->decimal('refund_amount', 15, 2)->default(0);
            $table->timestampTz('refunded_at')->nullable();
            $table->foreignUuid('received_by')->constrained('users');
            $table->text('notes')->nullable();
            $table->timestampsTz();

            $table->index('patient_id');
            $table->index('payment_date');
        });

        Schema::create('ledger_entries', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('patient_id')->constrained('patients')->onDelete('cascade');
            $table->foreignUuid('location_id')->constrained('locations');
            $table->enum('entry_type', ['charge', 'payment', 'adjustment', 'refund', 'transfer'])->default('charge');
            $table->date('entry_date');
            $table->decimal('amount', 15, 2); // positive=debit, negative=credit
            $table->decimal('balance_running', 15, 2);
            $table->string('description', 255);
            $table->string('cdt_code', 20)->nullable();
            $table->foreignUuid('appointment_id')->nullable()->constrained('appointments');
            $table->foreignUuid('claim_id')->nullable()->constrained('claims');
            $table->foreignUuid('payment_id')->nullable()->constrained('payments');
            $table->foreignUuid('adjustment_type_id')->nullable()->constrained('adjustment_types');
            $table->foreignUuid('created_by')->constrained('users');
            $table->boolean('is_void')->default(false);
            $table->timestampTz('voided_at')->nullable();
            $table->foreignUuid('voided_by')->nullable()->constrained('users');
            $table->text('notes')->nullable();
            $table->timestampsTz();

            $table->index('patient_id');
            $table->index('entry_date');
        });

        Schema::create('statements', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('patient_id')->constrained('patients')->onDelete('cascade');
            $table->date('statement_date');
            $table->date('due_date');
            $table->decimal('balance_forward', 15, 2)->default(0);
            $table->decimal('new_charges', 15, 2)->default(0);
            $table->decimal('payments_received', 15, 2)->default(0);
            $table->decimal('adjustments', 15, 2)->default(0);
            $table->decimal('current_balance', 15, 2);
            $table->enum('status', ['draft', 'sent', 'viewed', 'paid', 'overdue'])->default('draft');
            $table->timestampTz('sent_at')->nullable();
            $table->json('sent_via')->nullable(); // channels
            $table->timestampTz('viewed_at')->nullable();
            $table->timestampTz('paid_at')->nullable();
            $table->string('file_url', 500)->nullable();
            $table->timestampsTz();

            $table->index('patient_id');
            $table->index('statement_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('statements');
        Schema::dropIfExists('ledger_entries');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('payment_plans');
        Schema::dropIfExists('adjustment_types');
    }
};
