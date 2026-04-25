<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponseTrait;
use App\Models\AdjustmentType;
use App\Models\LedgerEntry;
use App\Models\Patient;
use App\Models\Payment;
use App\Models\PaymentPlan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BillingController extends Controller
{
    use ApiResponseTrait;

    public function ledger(Patient $patient): JsonResponse
    {
        $entries = $patient->ledgerEntries()
            ->with(['cdtCode', 'appointment', 'claim', 'payment', 'adjustmentType'])
            ->orderBy('entry_date')
            ->orderBy('created_at')
            ->get();

        // Calculate running balance
        $balance = 0;
        $entries = $entries->map(function ($entry) use (&$balance) {
            if (!$entry->is_void) {
                $balance += (float) $entry->amount;
            }
            $entry->running_balance = $balance;
            return $entry;
        });

        return $this->success([
            'entries' => $entries,
            'current_balance' => $balance,
        ]);
    }

    public function postCharge(Request $request, Patient $patient): JsonResponse
    {
        $validated = $request->validate([
            'entry_date' => 'required|date',
            'amount' => 'required|numeric',
            'description' => 'required|string',
            'cdt_code_id' => 'nullable|exists:cdt_codes,id',
            'appointment_id' => 'nullable|exists:appointments,id',
            'notes' => 'nullable|string',
        ]);

        $entry = LedgerEntry::create(array_merge($validated, [
            'patient_id' => $patient->id,
            'location_id' => $patient->primary_location_id,
            'entry_type' => 'charge',
            'created_by' => $request->user()->id,
        ]));

        return $this->success($entry, 'Charge posted successfully');
    }

    public function postAdjustment(Request $request, Patient $patient): JsonResponse
    {
        $validated = $request->validate([
            'entry_date' => 'required|date',
            'amount' => 'required|numeric', // Negative for credit, positive for debit
            'adjustment_type_id' => 'required|exists:adjustment_types,id',
            'description' => 'required|string',
            'parent_entry_id' => 'nullable|exists:ledger_entries,id',
            'notes' => 'nullable|string',
        ]);

        $entry = LedgerEntry::create(array_merge($validated, [
            'patient_id' => $patient->id,
            'location_id' => $patient->primary_location_id,
            'entry_type' => 'adjustment',
            'created_by' => $request->user()->id,
        ]));

        return $this->success($entry, 'Adjustment posted successfully');
    }

    public function voidEntry(Request $request, LedgerEntry $entry): JsonResponse
    {
        if ($entry->is_void) {
            return $this->error('Entry is already voided.', 422);
        }

        $entry->update([
            'is_void' => true,
            'voided_at' => now(),
            'voided_by' => $request->user()->id,
            'notes' => $entry->notes . "\nVoided: " . $request->void_reason,
        ]);

        return $this->success($entry, 'Entry voided successfully');
    }

    public function dashboardKpis(): JsonResponse
    {
        $today = now()->startOfDay();
        $monthStart = now()->startOfMonth();

        $collectionsToday = Payment::where('payment_date', '>=', $today)->sum('amount');
        $collectionsMtd = Payment::where('payment_date', '>=', $monthStart)->sum('amount');
        
        $productionToday = LedgerEntry::where('entry_type', 'charge')
            ->where('entry_date', '>=', $today)
            ->where('is_void', false)
            ->sum('amount');
        
        $productionMtd = LedgerEntry::where('entry_type', 'charge')
            ->where('entry_date', '>=', $monthStart)
            ->where('is_void', false)
            ->sum('amount');

        $totalAr = LedgerEntry::where('is_void', false)->sum('amount');

        return $this->success([
            'today_collections' => (float) $collectionsToday,
            'mtd_collections' => (float) $collectionsMtd,
            'today_production' => (float) $productionToday,
            'mtd_production' => (float) $productionMtd,
            'total_ar' => (float) $totalAr,
        ]);
    }

    public function agingReport(): JsonResponse
    {
        // Simple aging buckets
        $now = now();
        $buckets = [
            '0_30' => 0,
            '31_60' => 0,
            '61_90' => 0,
            '91_plus' => 0,
        ];

        $entries = LedgerEntry::where('is_void', false)
            ->where('entry_type', 'charge')
            ->get();

        foreach ($entries as $entry) {
            $days = $entry->entry_date->diffInDays($now);
            if ($days <= 30) $buckets['0_30'] += $entry->amount;
            elseif ($days <= 60) $buckets['31_60'] += $entry->amount;
            elseif ($days <= 90) $buckets['61_90'] += $entry->amount;
            else $buckets['91_plus'] += $entry->amount;
        }

        // Adjust for payments (simplified: subtract from oldest first or just global offset)
        // In real system, we'd apply payments to specific charges.
        
        return $this->success($buckets);
    }
}
