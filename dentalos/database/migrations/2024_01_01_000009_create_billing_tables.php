<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('adjustment_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
            $table->string('name');
            $table->string('code');
            $table->boolean('is_debit')->default(false);
            $table->boolean('affects_production')->default(false);
            $table->boolean('affects_collections')->default(false);
            $table->boolean('requires_approval')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('ledger_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained('patients')->onDelete('cascade');
            $table->foreignId('location_id')->constrained('locations')->onDelete('cascade');
            $table->enum('entry_type', ['charge', 'payment', 'adjustment', 'refund', 'transfer']);
            $table->date('entry_date');
            $table->decimal('amount', 10, 2); // positive=debit, negative=credit
            $table->string('description');
            $table->foreignId('cdt_code_id')->nullable()->constrained('cdt_codes')->onDelete('set null');
            $table->foreignId('appointment_id')->nullable()->constrained('appointments')->onDelete('set null');
            $table->foreignId('claim_id')->nullable()->constrained('claims')->onDelete('set null');
            $table->bigInteger('payment_id')->nullable();
            $table->foreignId('adjustment_type_id')->nullable()->constrained('adjustment_types')->onDelete('set null');
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->boolean('is_void')->default(false);
            $table->timestamp('voided_at')->nullable();
            $table->foreignId('voided_by')->nullable()->constrained('users')->onDelete('set null');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['patient_id', 'entry_date']);
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained('patients')->onDelete('cascade');
            $table->foreignId('location_id')->constrained('locations')->onDelete('cascade');
            $table->date('payment_date');
            $table->decimal('amount', 10, 2);
            $table->enum('payment_method', ['cash', 'check', 'credit_card', 'debit_card', 'ach', 'hsa_fsa', 'financing', 'other']);
            $table->string('card_last4', 4)->nullable();
            $table->string('card_brand')->nullable();
            $table->string('check_number')->nullable();
            $table->string('transaction_id')->nullable(); // from Stripe
            $table->string('stripe_payment_intent_id')->nullable();
            $table->json('processor_response')->nullable();
            $table->bigInteger('payment_plan_id')->nullable();
            $table->boolean('is_refunded')->default(false);
            $table->decimal('refund_amount', 10, 2)->default(0);
            $table->timestamp('refunded_at')->nullable();
            $table->foreignId('received_by')->constrained('users')->onDelete('cascade');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('payment_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained('patients')->onDelete('cascade');
            $table->decimal('total_amount', 10, 2);
            $table->decimal('down_payment', 10, 2)->default(0);
            $table->decimal('installment_amount', 10, 2);
            $table->enum('frequency', ['weekly', 'biweekly', 'monthly'])->default('monthly');
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->integer('number_of_payments');
            $table->integer('payments_made')->default(0);
            $table->decimal('remaining_balance', 10, 2);
            $table->enum('status', ['active', 'completed', 'defaulted', 'cancelled'])->default('active');
            $table->boolean('auto_debit_enabled')->default(false);
            $table->string('auto_debit_stripe_payment_method_id')->nullable();
            $table->tinyInteger('auto_debit_day_of_month')->nullable();
            $table->enum('financing_provider', ['internal', 'carecredit', 'sunbit', 'cherry', 'lendingclub', 'other'])->nullable();
            $table->string('financing_account_number')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });

        Schema::create('statements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained('patients')->onDelete('cascade');
            $table->date('statement_date');
            $table->date('due_date')->nullable();
            $table->decimal('balance_forward', 10, 2)->default(0);
            $table->decimal('new_charges', 10, 2)->default(0);
            $table->decimal('payments_received', 10, 2)->default(0);
            $table->decimal('adjustments', 10, 2)->default(0);
            $table->decimal('current_balance', 10, 2)->default(0);
            $table->enum('status', ['draft', 'sent', 'viewed', 'paid', 'overdue'])->default('draft');
            $table->timestamp('sent_at')->nullable();
            $table->json('sent_via')->nullable(); // array of channels
            $table->timestamp('viewed_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->string('file_path')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('statements');
        Schema::dropIfExists('payment_plans');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('ledger_entries');
        Schema::dropIfExists('adjustment_types');
    }
};
