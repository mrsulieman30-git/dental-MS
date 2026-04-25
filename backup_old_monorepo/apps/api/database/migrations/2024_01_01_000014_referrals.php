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
        Schema::create('referral_contacts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->constrained('tenants')->onDelete('cascade');
            $table->string('name', 255);
            $table->string('specialty', 100)->nullable();
            $table->string('practice_name', 255)->nullable();
            $table->string('phone', 50)->nullable();
            $table->string('fax', 50)->nullable();
            $table->string('email', 255)->nullable();
            $table->json('address')->nullable();
            $table->string('npi_number', 20)->nullable();
            $table->boolean('is_preferred')->default(false);
            $table->text('notes')->nullable();
            $table->timestampsTz();

            $table->index('tenant_id');
        });

        Schema::create('referrals', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->constrained('tenants')->onDelete('cascade');
            $table->foreignUuid('patient_id')->constrained('patients')->onDelete('cascade');
            $table->foreignUuid('referring_provider_id')->constrained('users');
            $table->foreignUuid('receiving_provider_id')->nullable()->constrained('users'); // internal
            $table->foreignUuid('referral_contact_id')->nullable()->constrained('referral_contacts'); // external
            
            $table->enum('direction', ['outgoing', 'incoming'])->default('outgoing');
            $table->text('reason')->nullable();
            $table->json('cdt_codes')->nullable();
            $table->text('clinical_notes')->nullable();
            $table->string('referral_letter_url', 500)->nullable();
            $table->json('attachments')->nullable(); // file URLs
            
            $table->enum('status', [
                'sent', 'acknowledged', 'seen', 'completed', 'cancelled', 'expired'
            ])->default('sent');
            
            $table->date('sent_date')->nullable();
            $table->date('due_back_date')->nullable();
            $table->date('seen_date')->nullable();
            $table->date('completed_date')->nullable();
            
            $table->string('return_letter_url', 500)->nullable();
            $table->text('response_notes')->nullable();
            
            $table->timestampsTz();
            $table->softDeletesTz();

            $table->index('tenant_id');
            $table->index('patient_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('referrals');
        Schema::dropIfExists('referral_contacts');
    }
};
