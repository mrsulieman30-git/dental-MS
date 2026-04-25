<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('referral_contacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
            $table->string('name');
            $table->string('specialty')->nullable();
            $table->string('practice_name')->nullable();
            $table->string('phone')->nullable();
            $table->string('fax')->nullable();
            $table->string('email')->nullable();
            $table->json('address')->nullable();
            $table->string('npi_number')->nullable();
            $table->boolean('is_preferred')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('referrals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
            $table->foreignId('patient_id')->constrained('patients')->onDelete('cascade');
            $table->foreignId('referring_provider_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('receiving_provider_id')->nullable()->constrained('users')->onDelete('set null'); // internal
            $table->foreignId('referral_contact_id')->nullable()->constrained('referral_contacts')->onDelete('set null'); // external specialist
            $table->enum('direction', ['outgoing', 'incoming'])->default('outgoing');
            $table->text('reason');
            $table->json('cdt_codes')->nullable();
            $table->text('clinical_notes')->nullable();
            $table->string('referral_letter_path')->nullable();
            $table->json('attachments')->nullable();
            $table->enum('status', ['sent', 'acknowledged', 'seen', 'completed', 'cancelled', 'expired'])->default('sent');
            $table->date('sent_date')->nullable();
            $table->date('due_back_date')->nullable();
            $table->date('seen_date')->nullable();
            $table->date('completed_date')->nullable();
            $table->string('return_letter_path')->nullable();
            $table->text('response_notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('referrals');
        Schema::dropIfExists('referral_contacts');
    }
};
