<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponseTrait;
use App\Models\Era;
use App\Models\Claim;
use App\Models\ClaimLineItem;
use App\Models\LedgerEntry;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class EraController extends Controller
{
    use ApiResponseTrait;

    public function index(): JsonResponse
    {
        return $this->success(Era::orderByDesc('received_at')->get());
    }

    public function upload(Request $request): JsonResponse
    {
        $request->validate(['file' => 'required|file']);
        
        $file = $request->file('file');
        $content = file_get_contents($file->path());
        
        // Mock parsing logic for 835 EDI file
        // In a real app, use a specialized EDI parser
        $eraData = [
            'tenant_id' => $request->user()->tenant_id,
            'file_name' => $file->getClientOriginalName(),
            'received_at' => now(),
            'payer_name' => 'Mock Insurance Co',
            'payer_id' => 'MOCK001',
            'check_number' => 'CHK-' . rand(10000, 99999),
            'check_date' => now()->subDays(2),
            'total_payment' => 1250.00,
            'raw_data' => ['content' => base64_encode($content)],
        ];

        $era = Era::create($eraData);

        return $this->success($era, 'ERA uploaded and parsed successfully');
    }

    /**
     * Post an ERA to the ledger and claims.
     */
    public function post(Request $request, Era $era): JsonResponse
    {
        if ($era->is_posted) {
            return $this->error('ERA already posted.', 422);
        }

        return DB::transaction(function () use ($request, $era) {
            // Find claims related to this ERA (Simulated lookup)
            // In a real app, the 835 file contains CLT (Claim) segments with claim numbers
            $claims = Claim::where('status', 'submitted')
                ->where('tenant_id', $era->tenant_id)
                ->limit(3) // Mock 3 claims per ERA
                ->get();

            foreach ($claims as $claim) {
                $paymentAmount = $claim->total_billed * 0.8; // Mock 80% coverage
                $writeOff = $claim->total_billed * 0.1; // Mock 10% write-off

                $claim->update([
                    'status' => 'paid',
                    'total_paid' => $claim->total_paid + $paymentAmount,
                    'write_off' => $claim->write_off + $writeOff,
                    'paid_at' => now(),
                    'check_number' => $era->check_number,
                    'check_date' => $era->check_date,
                    'era_id' => $era->id,
                ]);

                // Update Line Items
                foreach ($claim->lineItems as $item) {
                    $item->update([
                        'insurance_paid' => $item->fee_billed * 0.8,
                        'adjustment' => $item->fee_billed * 0.1,
                        'adjustment_type' => 'Contractual Write-off',
                        'status' => 'paid',
                    ]);
                }

                // Post to Ledger
                LedgerEntry::create([
                    'patient_id' => $claim->patient_id,
                    'location_id' => $claim->location_id,
                    'entry_type' => 'payment',
                    'entry_date' => now(),
                    'amount' => -$paymentAmount,
                    'description' => "Insurance Payment - {$era->payer_name} (ERA #{$era->id})",
                    'claim_id' => $claim->id,
                    'created_by' => $request->user()->id,
                ]);

                LedgerEntry::create([
                    'patient_id' => $claim->patient_id,
                    'location_id' => $claim->location_id,
                    'entry_type' => 'adjustment',
                    'entry_date' => now(),
                    'amount' => -$writeOff,
                    'description' => "Insurance Write-off - {$era->payer_name}",
                    'claim_id' => $claim->id,
                    'created_by' => $request->user()->id,
                ]);
            }

            $era->update([
                'is_posted' => true,
                'posted_at' => now(),
                'posted_by' => $request->user()->id,
            ]);

            return $this->success($era, 'ERA posted to ledger and claims');
        });
    }
}
