<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponseTrait;
use App\Models\Patient;
use App\Models\PatientInsurance;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InsuranceController extends Controller
{
    use ApiResponseTrait;

    public function index(Patient $patient): JsonResponse
    {
        $insurances = $patient->insurances()
            ->with('carrier')
            ->orderBy('sequence')
            ->get();

        return $this->success($insurances);
    }

    public function store(Request $request, Patient $patient): JsonResponse
    {
        $validated = $request->validate([
            'carrier_id' => 'required|exists:insurance_carriers,id',
            'plan_name' => 'required|string|max:255',
            'group_number' => 'nullable|string|max:50',
            'subscriber_id' => 'required|string|max:50',
            'subscriber_name' => 'required|string|max:255',
            'subscriber_dob' => 'nullable|date',
            'subscriber_relationship' => 'required|in:self,spouse,child,other',
            'employer_name' => 'nullable|string|max:255',
            'effective_date' => 'nullable|date',
            'termination_date' => 'nullable|date',
            'is_primary' => 'boolean',
            'sequence' => 'integer|min:1|max:3',
            'annual_maximum' => 'nullable|numeric',
            'deductible_individual' => 'nullable|numeric',
            'deductible_family' => 'nullable|numeric',
            'deductible_met' => 'nullable|numeric',
            'benefits_used_ytd' => 'nullable|numeric',
            'benefit_year_start' => 'nullable|integer|min:1|max:12',
            'waiting_periods' => 'nullable|array',
            'covered_percentages' => 'nullable|array',
            'missing_tooth_clause' => 'boolean',
            'coordination_of_benefits_type' => 'nullable|string',
            'pre_auth_required_above' => 'nullable|numeric',
        ]);

        $insurance = PatientInsurance::create(
            array_merge($validated, ['patient_id' => $patient->id])
        );

        return $this->success($insurance->load('carrier'), 'Insurance added', 201);
    }

    public function update(Request $request, Patient $patient, PatientInsurance $insurance): JsonResponse
    {
        $validated = $request->validate([
            'carrier_id' => 'sometimes|exists:insurance_carriers,id',
            'plan_name' => 'sometimes|string|max:255',
            'group_number' => 'nullable|string|max:50',
            'subscriber_id' => 'sometimes|string|max:50',
            'subscriber_name' => 'sometimes|string|max:255',
            'subscriber_dob' => 'nullable|date',
            'subscriber_relationship' => 'sometimes|in:self,spouse,child,other',
            'employer_name' => 'nullable|string|max:255',
            'effective_date' => 'nullable|date',
            'termination_date' => 'nullable|date',
            'is_primary' => 'boolean',
            'sequence' => 'integer|min:1|max:3',
            'annual_maximum' => 'nullable|numeric',
            'deductible_individual' => 'nullable|numeric',
            'deductible_family' => 'nullable|numeric',
            'deductible_met' => 'nullable|numeric',
            'benefits_used_ytd' => 'nullable|numeric',
            'benefit_year_start' => 'nullable|integer|min:1|max:12',
            'waiting_periods' => 'nullable|array',
            'covered_percentages' => 'nullable|array',
            'missing_tooth_clause' => 'boolean',
            'coordination_of_benefits_type' => 'nullable|string',
            'pre_auth_required_above' => 'nullable|numeric',
        ]);

        $insurance->update($validated);

        return $this->success($insurance->fresh()->load('carrier'), 'Insurance updated');
    }

    public function destroy(Patient $patient, PatientInsurance $insurance): JsonResponse
    {
        $insurance->delete();
        return $this->success(null, 'Insurance removed');
    }

    /**
     * Simulate eligibility verification.
     * In production, this would call a clearinghouse 270/271 EDI endpoint.
     */
    public function verify(Request $request, Patient $patient, PatientInsurance $insurance): JsonResponse
    {
        // Simulate eligibility check (would be real EDI call in production)
        $verificationResult = [
            'verified' => true,
            'verification_date' => now()->toISOString(),
            'verified_by' => $request->user()->full_name ?? $request->user()->name,
            'plan_status' => 'active',
            'subscriber_name' => $insurance->subscriber_name,
            'group_number' => $insurance->group_number,
            'annual_maximum' => $insurance->annual_maximum ?? 1500.00,
            'deductible_individual' => $insurance->deductible_individual ?? 50.00,
            'deductible_remaining' => ($insurance->deductible_individual ?? 50.00) - ($insurance->deductible_met ?? 0),
            'benefits_used_ytd' => $insurance->benefits_used_ytd ?? 0,
            'benefits_remaining' => ($insurance->annual_maximum ?? 1500.00) - ($insurance->benefits_used_ytd ?? 0),
            'coverage' => $insurance->covered_percentages ?? [
                'preventive' => 100,
                'basic' => 80,
                'major' => 50,
                'orthodontics' => 50,
                'implants' => 0,
            ],
            'waiting_periods' => $insurance->waiting_periods ?? [
                'preventive' => 'None',
                'basic' => 'None',
                'major' => '12 months',
                'orthodontics' => '12 months',
            ],
        ];

        // Append to eligibility history
        $history = $insurance->eligibility_history ?? [];
        $history[] = [
            'date' => now()->toISOString(),
            'verified_by' => $request->user()->name ?? 'System',
            'result' => $verificationResult,
            'summary' => 'Plan active. Annual max: $' . number_format($verificationResult['annual_maximum'], 2) .
                '. Remaining: $' . number_format($verificationResult['benefits_remaining'], 2),
        ];

        $insurance->update([
            'verified_at' => now(),
            'verified_by' => $request->user()->id,
            'eligibility_response' => $verificationResult,
            'eligibility_history' => $history,
        ]);

        return $this->success($insurance->fresh()->load('carrier'), 'Eligibility verified');
    }

    public function eligibilityHistory(Patient $patient, PatientInsurance $insurance): JsonResponse
    {
        return $this->success([
            'history' => $insurance->eligibility_history ?? [],
            'last_verified' => $insurance->verified_at,
        ]);
    }
}
