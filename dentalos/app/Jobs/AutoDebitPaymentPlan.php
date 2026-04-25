<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\PaymentPlan;
use App\Models\LedgerEntry;
use App\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Stripe\StripeClient;

class AutoDebitPaymentPlan implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        $today = now()->day;
        
        $plans = PaymentPlan::where('auto_debit_enabled', true)
            ->where('status', 'active')
            ->where('debit_day_of_month', $today)
            ->get();

        $stripe = new StripeClient(config('services.stripe.secret'));

        foreach ($plans as $plan) {
            try {
                $charge = $stripe->paymentIntents->create([
                    'amount' => (int)($plan->installment_amount * 100),
                    'currency' => 'usd',
                    'customer' => $plan->patient->stripe_customer_id,
                    'payment_method' => $plan->stripe_payment_method_id,
                    'off_session' => true,
                    'confirm' => true,
                ]);

                if ($charge->status === 'succeeded') {
                    $payment = Payment::create([
                        'tenant_id' => $plan->tenant_id,
                        'patient_id' => $plan->patient_id,
                        'amount' => $plan->installment_amount,
                        'payment_method' => 'stripe',
                        'payment_date' => now(),
                        'reference_number' => $charge->id,
                    ]);

                    LedgerEntry::create([
                        'patient_id' => $plan->patient_id,
                        'entry_type' => 'payment',
                        'amount' => -$plan->installment_amount,
                        'description' => 'Auto-debit Payment Plan: ' . $plan->id,
                        'payment_id' => $payment->id,
                    ]);
                }
            } catch (\Exception $e) {
                // Log failure and notify staff
            }
        }
    }
}
