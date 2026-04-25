<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponseTrait;
use App\Models\DentalChart;
use App\Models\Patient;
use App\Models\ToothCondition;
use App\Models\Restoration;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ChartController extends Controller
{
    use ApiResponseTrait;

    public function show(Patient $patient): JsonResponse
    {
        Gate::authorize('view', $patient);
        
        $chart = $patient->chart ?: DentalChart::create(['patient_id' => $patient->id]);
        
        return $this->success($chart->load(['toothConditions', 'restorations', 'implants']));
    }

    public function storeCondition(Request $request, Patient $patient): JsonResponse
    {
        Gate::authorize('view', $patient);

        $chart = $patient->chart ?: DentalChart::create(['patient_id' => $patient->id]);
        $validated = $request->validate([
            'tooth_number' => 'required|integer',
            'surfaces' => 'nullable|array',
            'surfaces.*' => 'in:M,D,O,B,L,I,F',
            'condition_type' => 'required|in:caries,fracture,wear,sensitivity,mobility,peri_implantitis,perio,other',
            'severity' => 'nullable|in:initial,moderate,severe,watch',
            'status' => 'required|in:existing,proposed,in_progress,completed,declined,referred,monitored',
            'cdt_code' => 'nullable|string|max:20',
            'notes' => 'nullable|string',
            'appointment_id' => 'nullable|exists:appointments,id',
        ]);

        $condition = ToothCondition::create($validated + [
            'dental_chart_id' => $chart->id,
            'diagnosed_by' => $request->user()->id,
            'diagnosed_date' => now(),
        ]);

        return $this->success($condition, 'Condition added', 201);
    }

    public function storeRestoration(Request $request, Patient $patient): JsonResponse
    {
        Gate::authorize('view', $patient);

        $chart = $patient->chart ?: DentalChart::create(['patient_id' => $patient->id]);
        $validated = $request->validate([
            'tooth_number' => 'required|integer',
            'surfaces' => 'nullable|array',
            'surfaces.*' => 'in:M,D,O,B,L,I,F',
            'restoration_type' => 'required|in:filling,crown,bridge,implant,veneer,onlay,inlay,denture_partial,denture_full,sealant,rct,buildup,post_core,other',
            'material' => 'nullable|in:amalgam,composite,gold,porcelain,zirconia,pfm,acrylic,other',
            'shade' => 'nullable|string|max:50',
            'tooth_position' => 'nullable|in:single,abutment,pontic',
            'bridge_teeth' => 'nullable|array',
            'bridge_teeth.*' => 'integer|between:1,32',
            'lab_case_id' => 'nullable|integer',
            'placement_date' => 'nullable|date',
            'notes' => 'nullable|string',
            'appointment_id' => 'nullable|exists:appointments,id',
            'status' => 'required|in:existing,new,needs_replacement,failed',
        ]);

        $restoration = Restoration::create($validated + [
            'dental_chart_id' => $chart->id,
            'placed_by' => $request->user()->id,
            'placement_date' => $validated['placement_date'] ?? now(),
        ]);

        return $this->success($restoration, 'Restoration added', 201);
    }
}
