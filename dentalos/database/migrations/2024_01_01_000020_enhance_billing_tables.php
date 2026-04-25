<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->string('stripe_customer_id')->nullable()->after('portal_user_id');
            $table->string('stripe_payment_method_id')->nullable()->after('stripe_customer_id');
            $table->timestamp('last_statement_at')->nullable()->after('billing_notes');
        });

        Schema::table('claims', function (Blueprint $table) {
            $table->boolean('is_scrubbed')->default(false)->after('status');
            $table->json('scrubbing_errors')->nullable()->after('is_scrubbed');
            $table->foreignId('secondary_claim_id')->nullable()->constrained('claims')->onDelete('set null')->after('era_id');
            $table->string('claim_form_path')->nullable()->after('secondary_claim_id');
        });

        Schema::table('ledger_entries', function (Blueprint $table) {
            $table->foreignId('parent_entry_id')->nullable()->constrained('ledger_entries')->onDelete('cascade')->after('notes');
        });
    }

    public function down(): void
    {
        Schema::table('ledger_entries', function (Blueprint $table) {
            $table->dropForeign(['parent_entry_id']);
            $table->dropColumn('parent_entry_id');
        });

        Schema::table('claims', function (Blueprint $table) {
            $table->dropForeign(['secondary_claim_id']);
            $table->dropColumn(['is_scrubbed', 'scrubbing_errors', 'secondary_claim_id', 'claim_form_path']);
        });

        Schema::table('patients', function (Blueprint $table) {
            $table->dropColumn(['stripe_customer_id', 'stripe_payment_method_id', 'last_statement_at']);
        });
    }
};
