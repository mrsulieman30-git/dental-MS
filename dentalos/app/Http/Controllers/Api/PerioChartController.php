<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponseTrait;
use App\Models\Patient;
use App\Models\PerioChart;
use App\Models\PerioMeasurement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class PerioChartController extends Controller
{
    use ApiResponseTrait;

    public function index(Patient $patient): JsonResponse
    {
        Gate::authorize('view', $patient);

        $charts = $patient->perioCharts()
            ->with('provider:id,first_name,last_name')
            ->latest('chart_date')
            ->get();

        return $this->success($charts);
    }

    public function store(Request $request, Patient $patient): JsonResponse
    {
        Gate::authorize('view', $patient);

        $validated = $request->validate([
            'appointment_id' => 'nullable|exists:appointments,id',
            'chart_date' => 'required|date',
            'notes' => 'nullable|string',
            'measurements' => 'required|array|min:1',
            'measurements.*.tooth_number' => 'required|integer|between:1,32',
            'measurements.*.surface' => 'required|in:buccal,lingual',
            'measurements.*.pos1_probe' => 'nullable|integer|between:0,12',
            'measurements.*.pos2_probe' => 'nullable|integer|between:0,12',
            'measurements.*.pos3_probe' => 'nullable|integer|between:0,12',
            'measurements.*.pos1_recession' => 'nullable|integer|between:0,10',
            'measurements.*.pos2_recession' => 'nullable|integer|between:0,10',
            'measurements.*.pos3_recession' => 'nullable|integer|between:0,10',
            'measurements.*.pos1_bleeding' => 'nullable|boolean',
            'measurements.*.pos2_bleeding' => 'nullable|boolean',
            'measurements.*.pos3_bleeding' => 'nullable|boolean',
            'measurements.*.pos1_suppuration' => 'nullable|boolean',
            'measurements.*.pos2_suppuration' => 'nullable|boolean',
            'measurements.*.pos3_suppuration' => 'nullable|boolean',
            'measurements.*.furcation_class' => 'nullable|in:none,I,II,III',
            'measurements.*.mobility_grade' => 'nullable|integer|between:0,3',
            'measurements.*.plaque_present' => 'nullable|boolean',
            'measurements.*.calculus_present' => 'nullable|boolean',
        ]);

        $appointmentId = $validated['appointment_id'] ?? $patient->appointments()->latest('start_time')->value('id');

        if (!$appointmentId) {
            throw ValidationException::withMessages([
                'appointment_id' => 'A periodontal chart must be linked to an appointment for this patient.',
            ]);
        }

        $aap = $this->calculateAapResult($validated['measurements']);

        $chart = PerioChart::create([
            'patient_id' => $patient->id,
            'appointment_id' => $appointmentId,
            'provider_id' => $request->user()->id,
            'chart_date' => $validated['chart_date'],
            'aap_stage' => $aap['stage'],
            'aap_grade' => $aap['grade'],
            'overall_risk_level' => $aap['riskLevel'],
            'notes' => $validated['notes'] ?? null,
        ]);

        foreach ($validated['measurements'] as $measurement) {
            PerioMeasurement::create($measurement + ['perio_chart_id' => $chart->id]);
        }

        return $this->success(
            $chart->load(['measurements', 'provider:id,first_name,last_name']),
            'Perio chart saved',
            201
        );
    }

    public function show(PerioChart $perioChart): JsonResponse
    {
        Gate::authorize('view', $perioChart->patient);

        return $this->success($perioChart->load(['measurements', 'provider:id,first_name,last_name']));
    }

    public function compare(PerioChart $perioChart, PerioChart $compareChart): JsonResponse
    {
        Gate::authorize('view', $perioChart->patient);

        if ($perioChart->patient_id !== $compareChart->patient_id) {
            return $this->error('Both periodontal charts must belong to the same patient.', [], 422);
        }

        return $this->success([
            'baseline' => $perioChart->load(['measurements', 'provider:id,first_name,last_name']),
            'comparison' => $compareChart->load(['measurements', 'provider:id,first_name,last_name']),
        ]);
    }

    private function calculateAapResult(array $measurements): array
    {
        $maxProbe = 0;
        $maxCal = 0;
        $bleedingSites = 0;
        $totalSites = 0;

        foreach ($measurements as $measurement) {
            foreach ([1, 2, 3] as $position) {
                $probe = $measurement["pos{$position}_probe"] ?? null;
                $recession = $measurement["pos{$position}_recession"] ?? 0;

                if ($probe === null) {
                    continue;
                }

                $maxProbe = max($maxProbe, (int) $probe);
                $maxCal = max($maxCal, (int) $probe + (int) $recession);
                $totalSites++;

                if (!empty($measurement["pos{$position}_bleeding"])) {
                    $bleedingSites++;
                }
            }
        }

        $bop = $totalSites > 0 ? (int) round(($bleedingSites / $totalSites) * 100) : 0;

        if ($maxCal <= 1 && $maxProbe <= 4) {
            $stage = 'I';
        } elseif ($maxCal <= 2 && $maxProbe <= 5) {
            $stage = 'II';
        } elseif ($maxProbe <= 6) {
            $stage = 'III';
        } else {
            $stage = 'IV';
        }

        if ($bop < 10) {
            $grade = 'A';
        } elseif ($bop < 30) {
            $grade = 'B';
        } else {
            $grade = 'C';
        }

        $riskLevel = match ($stage) {
            'I' => 'low',
            'II' => 'moderate',
            'III' => 'high',
            default => 'very_high',
        };

        return [
            'stage' => $stage,
            'grade' => $grade,
            'riskLevel' => $riskLevel,
            'maxProbe' => $maxProbe,
            'maxCal' => $maxCal,
            'bop' => $bop,
        ];
    }
}
