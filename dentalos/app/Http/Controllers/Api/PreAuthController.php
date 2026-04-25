<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponseTrait;
use App\Models\Patient;
use App\Models\PreAuthorization;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PreAuthController extends Controller
{
    use ApiResponseTrait;

    public function index(Patient $patient): JsonResponse
    {
        $preAuths = PreAuthorization::where('patient_id', $patient->id)
            ->with(['insurance.carrier', 'cdtCode', 'creator'])
            ->orderByDesc('created_at')
            ->get();

        return $this->success($preAuths);
    }

    public function store(Request $request, Patient $patient): JsonResponse
    {
        $validated = $request->validate([
            'insurance_id' => 'required|exists:patient_insurance,id',
            'cdt_code_id' => 'nullable|exists:cdt_codes,id',
            'procedure_description' => 'required|string|max:500',
            'tooth_number' => 'nullable|integer|min:1|max:32',
            'notes' => 'nullable|string',
        ]);

        $preAuth = PreAuthorization::create(array_merge($validated, [
            'tenant_id' => 1, // TODO: get from auth context
            'patient_id' => $patient->id,
            'status' => 'pending',
            'submitted_at' => now(),
            'created_by' => $request->user()->id,
        ]));

        return $this->success($preAuth->load(['insurance.carrier', 'cdtCode']), 'Pre-authorization request created', 201);
    }

    public function update(Request $request, PreAuthorization $preAuth): JsonResponse
    {
        $validated = $request->validate([
            'status' => 'sometimes|in:pending,approved,denied,expired',
            'auth_number' => 'nullable|string|max:50',
            'expiry_date' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        if (isset($validated['status']) && in_array($validated['status'], ['approved', 'denied'])) {
            $validated['response_at'] = now();
        }

        $preAuth->update($validated);

        return $this->success($preAuth->fresh()->load(['insurance.carrier', 'cdtCode']), 'Pre-authorization updated');
    }

    public function destroy(PreAuthorization $preAuth): JsonResponse
    {
        $preAuth->delete();
        return $this->success(null, 'Pre-authorization deleted');
    }
}
