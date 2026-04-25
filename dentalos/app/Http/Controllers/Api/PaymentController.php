<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponseTrait;
use App\Models\LedgerEntry;
use App\Models\Patient;
use App\Models\Payment;
use App\Models\PaymentPlan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Stripe\Stripe;
use Stripe\PaymentIntent;

class PaymentController extends Controller
{
    use ApiResponseTrait;

    public function __construct()
    {
        Stripe::setApiKey(config('services.stripe.secret') ?? env('STRIPE_SECRET'));
    }

    public function store(Request $request, Patient $patient): JsonResponse
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|in:cash,check,credit_card,debit_card,ach,hsa_fsa,financing,other',
            'payment_date' => 'required|date',
            'check_number' => 'nullable|string',
            'notes' => 'nullable|string',
            'stripe_payment_method_id' => 'nullable|string',
        ]);

        return DB::transaction(function () use ($request, $patient, $validated) {
            $paymentData = array_merge($validated, [
                'patient_id' => $patient->id,
                'location_id' => $patient->primary_location_id,
                'received_by' => $request->user()->id,
            ]);

            // Handle Stripe Payment
            if ($request->payment_method === 'credit_card' && $request->stripe_payment_method_id) {
                try {
                    $intent = PaymentIntent::create([
                        'amount' => (int) (round($validated['amount'], 2) * 100), // in cents
                        'currency' => 'usd',
                        'customer' => $patient->stripe_customer_id,
                        'payment_method' => $request->stripe_payment_method_id,
                        'confirm' => true,
                        'off_session' => false,
                        'automatic_payment_methods' => [
                            'enabled' => true,
                            'allow_redirects' => 'never',
                        ],
                    ]);
                    $paymentData['stripe_payment_intent_id'] = $intent->id;
                    $paymentData['transaction_id'] = $intent->id;
                } catch (\Exception $e) {
                    return $this->error('Stripe Payment Failed: ' . $e->getMessage(), 422);
                }
            }

            $payment = Payment::create($paymentData);

            // Post to Ledger
            LedgerEntry::create([
                'patient_id' => $patient->id,
                'location_id' => $patient->primary_location_id,
                'entry_type' => 'payment',
                'entry_date' => $validated['payment_date'],
                'amount' => -$validated['amount'], // Negative for credit
                'description' => 'Payment - ' . ucfirst($validated['payment_method']) . ($validated['check_number'] ? " #{$validated['check_number']}" : ""),
                'payment_id' => $payment->id,
                'created_by' => $request->user()->id,
            ]);

            return $this->success($payment, 'Payment posted successfully');
        });
    }

    public function createPaymentIntent(Request $request, Patient $patient): JsonResponse
    {
        $request->validate(['amount' => 'required|numeric|min:0.01']);

        try {
            // Ensure patient has stripe customer ID
            if (!$patient->stripe_customer_id) {
                $customer = \Stripe\Customer::create([
                    'name' => $patient->full_name,
                    'email' => $patient->emails()->first()?->email,
                    'metadata' => ['patient_id' => $patient->id]
                ]);
                $patient->update(['stripe_customer_id' => $customer->id]);
            }

            $intent = PaymentIntent::create([
                'amount' => (int) (round($request->amount, 2) * 100),
                'currency' => 'usd',
                'customer' => $patient->stripe_customer_id,
                'setup_future_usage' => 'off_session',
            ]);

            return $this->success(['client_secret' => $intent->client_secret]);
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 422);
        }
    }

    public function refund(Request $request, Payment $payment): JsonResponse
    {
        $request->validate(['amount' => 'required|numeric|min:0.01']);

        if ($request->amount > ($payment->amount - $payment->refund_amount)) {
            return $this->error('Refund amount exceeds remaining payment balance.', 422);
        }

        return DB::transaction(function () use ($request, $payment) {
            if ($payment->stripe_payment_intent_id) {
                try {
                    \Stripe\Refund::create([
                        'payment_intent' => $payment->stripe_payment_intent_id,
                        'amount' => (int) (round($request->amount, 2) * 100),
                    ]);
                } catch (\Exception $e) {
                    return $this->error('Stripe Refund Failed: ' . $e->getMessage(), 422);
                }
            }

            $payment->update([
                'is_refunded' => true,
                'refund_amount' => $payment->refund_amount + $request->amount,
                'refunded_at' => now(),
            ]);

            LedgerEntry::create([
                'patient_id' => $payment->patient_id,
                'location_id' => $payment->location_id,
                'entry_type' => 'refund',
                'entry_date' => now(),
                'amount' => $request->amount, // Positive for debit
                'description' => "Refund for Payment #{$payment->id}",
                'payment_id' => $payment->id,
                'created_by' => $request->user()->id,
            ]);

            return $this->success($payment, 'Refund processed successfully');
        });
    }
}
