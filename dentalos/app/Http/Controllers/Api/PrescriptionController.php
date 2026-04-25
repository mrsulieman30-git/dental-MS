<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponseTrait;
use App\Models\Prescription;
use App\Models\Patient;
use App\Services\AuditService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PrescriptionController extends Controller
{
    use ApiResponseTrait;

    public function __construct(protected AuditService $audit) {}

    public function index(Patient $patient): JsonResponse
    {
        return $this->success($patient->prescriptions()->with('prescriber')->get());
    }

    public function store(Request $request, Patient $patient): JsonResponse
    {
        $validated = $request->validate([
            'drug_name' => 'required|string',
            'dosage' => 'required|string',
            'quantity' => 'required|string',
            'sig' => 'required|string',
        ]);

        $rx = Prescription::create($validated + [
            'patient_id' => $patient->id,
            'prescriber_id' => $request->user()->id,
            'status' => 'active',
            'sent_at' => now()
        ]);

        $this->audit->log('prescribed', 'Prescription', $rx->id, null, $rx->toArray());

        return $this->success($rx, 'Prescription created', 201);
    }
}
