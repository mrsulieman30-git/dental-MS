<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponseTrait;
use App\Models\Payment;
use App\Models\LedgerEntry;
use App\Services\AuditService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    use ApiResponseTrait;

    public function __construct(protected AuditService $audit) {}

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'location_id' => 'required|exists:locations,id',
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|string',
            'check_number' => 'nullable|string',
        ]);

        $payment = Payment::create($validated + [
            'payment_date' => now(),
            'received_by' => $request->user()->id
        ]);

        // Create negative ledger entry for payment
        LedgerEntry::create([
            'patient_id' => $request->patient_id,
            'location_id' => $request->location_id,
            'entry_type' => 'payment',
            'entry_date' => now(),
            'amount' => -$request->amount,
            'description' => 'Payment - ' . $request->payment_method,
            'payment_id' => $payment->id,
            'created_by' => $request->user()->id
        ]);

        $this->audit->log('posted', 'Payment', $payment->id, null, $payment->toArray());

        return $this->success($payment, 'Payment posted successfully', 201);
    }
}
