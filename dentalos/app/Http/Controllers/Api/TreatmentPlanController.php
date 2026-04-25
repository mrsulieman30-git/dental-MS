<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponseTrait;
use App\Models\Patient;
use App\Models\TreatmentPlan;
use App\Models\TreatmentPlanProcedure;
use App\Models\CdtCode;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class TreatmentPlanController extends Controller
{
    use ApiResponseTrait;

    public function index(Patient $patient): JsonResponse
    {
        Gate::authorize('view', $patient);
        $plans = $patient->treatmentPlans()
            ->with('procedures.cdtCode', 'creator')
            ->orderByDesc('created_at')
            ->get();

        return $this->success($plans);
    }

    public function show(TreatmentPlan $plan): JsonResponse
    {
        $plan->load([
            'procedures.cdtCode',
            'creator',
            'patient.insurances.carrier',
            'location',
        ]);

        return $this->success($plan);
    }

    public function store(Request $request, Patient $patient): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'notes' => 'nullable|string',
            'phase_names' => 'nullable|array',
            'alternative_group' => 'nullable|integer',
        ]);

        $plan = TreatmentPlan::create([
            'patient_id' => $patient->id,
            'location_id' => $patient->primary_location_id,
            'name' => $validated['name'],
            'status' => 'draft',
            'created_by' => $request->user()->id,
            'version' => 1,
            'notes' => $validated['notes'] ?? null,
            'phase_names' => $validated['phase_names'] ?? ['Phase 1'],
            'alternative_group' => $validated['alternative_group'] ?? null,
        ]);

        return $this->success($plan->load('procedures'), 'Treatment plan created', 201);
    }

    public function update(Request $request, TreatmentPlan $plan): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'notes' => 'nullable|string',
            'phase_names' => 'nullable|array',
            'procedures' => 'sometimes|array',
            'procedures.*.id' => 'sometimes|integer',
            'procedures.*.phase' => 'required_with:procedures|integer',
            'procedures.*.sequence_order' => 'required_with:procedures|integer',
            'procedures.*.cdt_code_id' => 'required_with:procedures|integer',
            'procedures.*.procedure_name' => 'required_with:procedures|string',
            'procedures.*.tooth_number' => 'nullable|integer',
            'procedures.*.surfaces' => 'nullable|array',
            'procedures.*.fee' => 'required_with:procedures|numeric',
            'procedures.*.insurance_estimated' => 'nullable|numeric',
            'procedures.*.patient_portion' => 'nullable|numeric',
            'procedures.*.priority' => 'sometimes|in:immediate,soon,routine,elective',
            'procedures.*.notes' => 'nullable|string',
        ]);

        DB::transaction(function () use ($plan, $validated) {
            $plan->update(collect($validated)->only(['name', 'notes', 'phase_names'])->toArray());

            if (isset($validated['procedures'])) {
                $existingIds = [];

                foreach ($validated['procedures'] as $proc) {
                    if (isset($proc['id'])) {
                        TreatmentPlanProcedure::where('id', $proc['id'])
                            ->where('treatment_plan_id', $plan->id)
                            ->update($proc);
                        $existingIds[] = $proc['id'];
                    } else {
                        $created = TreatmentPlanProcedure::create(
                            array_merge($proc, ['treatment_plan_id' => $plan->id])
                        );
                        $existingIds[] = $created->id;
                    }
                }

                // Remove procedures not in the update payload
                TreatmentPlanProcedure::where('treatment_plan_id', $plan->id)
                    ->whereNotIn('id', $existingIds)
                    ->delete();

                // Recalculate totals
                $this->recalculateTotals($plan);
            }
        });

        return $this->success($plan->fresh()->load('procedures.cdtCode'), 'Treatment plan saved');
    }

    public function addProcedures(Request $request, TreatmentPlan $plan): JsonResponse
    {
        $request->validate(['procedures' => 'required|array']);

        foreach ($request->procedures as $p) {
            TreatmentPlanProcedure::create($p + ['treatment_plan_id' => $plan->id]);
        }

        $this->recalculateTotals($plan);

        return $this->success($plan->load('procedures.cdtCode'), 'Procedures added to plan');
    }

    public function updateStatus(Request $request, TreatmentPlan $plan): JsonResponse
    {
        $request->validate([
            'status' => 'required|in:draft,presented,accepted,declined,completed,in_progress',
            'signature' => 'nullable|string', // base64 signature image
            'signer_name' => 'nullable|string',
            'declined_procedures' => 'nullable|array',
            'declined_procedures.*' => 'integer',
        ]);

        $data = ['status' => $request->status];

        switch ($request->status) {
            case 'presented':
                $data['presented_at'] = now();
                break;
            case 'accepted':
                $data['accepted_at'] = now();
                $data['signed_by_patient'] = true;
                $data['patient_signed_at'] = now();
                $data['signer_name'] = $request->signer_name;
                $data['signer_ip'] = $request->ip();

                // Save signature image
                if ($request->signature) {
                    $imageData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $request->signature));
                    $path = 'signatures/plan_' . $plan->id . '_' . time() . '.png';
                    Storage::disk('local')->put($path, $imageData);
                    $data['signature_image_path'] = $path;
                }

                // Handle declined procedures
                if ($request->declined_procedures) {
                    TreatmentPlanProcedure::whereIn('id', $request->declined_procedures)
                        ->where('treatment_plan_id', $plan->id)
                        ->update([
                            'status' => 'declined',
                            'declined_at' => now(),
                        ]);
                }
                break;
            case 'declined':
                $data['declined_at'] = now();
                break;
        }

        $plan->update($data);

        return $this->success($plan->fresh()->load('procedures.cdtCode'), 'Plan status updated');
    }

    public function duplicate(TreatmentPlan $plan): JsonResponse
    {
        $newPlan = DB::transaction(function () use ($plan) {
            $clone = $plan->replicate(['id', 'created_at', 'updated_at', 'accepted_at', 'presented_at', 'declined_at', 'signed_by_patient', 'patient_signed_at', 'signature_image_path']);
            $clone->name = $plan->name . ' (Copy)';
            $clone->status = 'draft';
            $clone->version = $plan->version + 1;
            $clone->save();

            foreach ($plan->procedures as $procedure) {
                $procClone = $procedure->replicate(['id', 'created_at', 'updated_at']);
                $procClone->treatment_plan_id = $clone->id;
                $procClone->status = 'proposed';
                $procClone->save();
            }

            return $clone;
        });

        return $this->success($newPlan->load('procedures.cdtCode'), 'Treatment plan duplicated', 201);
    }

    public function archive(TreatmentPlan $plan): JsonResponse
    {
        $plan->delete(); // soft delete
        return $this->success(null, 'Treatment plan archived');
    }

    public function reorderProcedures(Request $request, TreatmentPlan $plan): JsonResponse
    {
        $request->validate([
            'procedures' => 'required|array',
            'procedures.*.id' => 'required|integer',
            'procedures.*.phase' => 'required|integer',
            'procedures.*.sequence_order' => 'required|integer',
        ]);

        DB::transaction(function () use ($request, $plan) {
            foreach ($request->procedures as $item) {
                TreatmentPlanProcedure::where('id', $item['id'])
                    ->where('treatment_plan_id', $plan->id)
                    ->update([
                        'phase' => $item['phase'],
                        'sequence_order' => $item['sequence_order'],
                    ]);
            }
        });

        return $this->success($plan->fresh()->load('procedures.cdtCode'), 'Procedures reordered');
    }

    public function present(TreatmentPlan $plan): JsonResponse
    {
        $plan->load([
            'procedures.cdtCode',
            'patient.insurances.carrier',
        ]);

        $primaryInsurance = $plan->patient->insurances->firstWhere('is_primary', true);

        $presentationData = [
            'plan' => $plan,
            'patient_name' => $plan->patient->full_name,
            'carrier_name' => $primaryInsurance?->carrier?->name ?? 'N/A',
            'total_fee' => $plan->total_fee,
            'total_insurance' => $plan->insurance_estimated,
            'total_patient' => $plan->patient_estimated,
            'phases' => $plan->procedures->groupBy('phase')->map(function ($procedures, $phase) use ($plan) {
                return [
                    'phase_number' => $phase,
                    'phase_name' => $plan->phase_names[$phase - 1] ?? "Phase {$phase}",
                    'procedures' => $procedures->map(function ($proc) {
                        return [
                            'id' => $proc->id,
                            'description' => $proc->cdtCode?->description ?? $proc->procedure_name,
                            'plain_name' => $proc->procedure_name,
                            'tooth_number' => $proc->tooth_number,
                            'surfaces' => $proc->surfaces,
                            'fee' => $proc->fee,
                            'insurance_estimated' => $proc->insurance_estimated,
                            'patient_portion' => $proc->patient_portion,
                            'priority' => $proc->priority,
                            'status' => $proc->status,
                        ];
                    }),
                    'subtotal_fee' => $procedures->sum('fee'),
                    'subtotal_insurance' => $procedures->sum('insurance_estimated'),
                    'subtotal_patient' => $procedures->sum('patient_portion'),
                ];
            })->values(),
        ];

        return $this->success($presentationData);
    }

    private function recalculateTotals(TreatmentPlan $plan): void
    {
        $procedures = $plan->procedures()->get();
        $plan->update([
            'total_fee' => $procedures->sum('fee'),
            'insurance_estimated' => $procedures->sum('insurance_estimated'),
            'patient_estimated' => $procedures->sum('patient_portion'),
        ]);
    }
}
