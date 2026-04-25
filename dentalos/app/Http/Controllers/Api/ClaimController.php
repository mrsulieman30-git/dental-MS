<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponseTrait;
use App\Models\Appointment;
use App\Models\Claim;
use App\Models\ClaimLineItem;
use App\Models\ClaimAttachment;
use App\Models\Patient;
use App\Models\TreatmentPlanProcedure;
use App\Models\CdtCode;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;

class ClaimController extends Controller
{
    use ApiResponseTrait;

    public function index(Request $request): JsonResponse
    {
        $query = Claim::with(['patient', 'insurance.carrier', 'renderingProvider'])
            ->orderByDesc('created_at');

        if ($request->has('status')) {
            $query->whereIn('status', explode(',', $request->status));
        }

        if ($request->has('patient_id')) {
            $query->where('patient_id', $request->patient_id);
        }

        return $this->success($query->paginate(20));
    }

    public function show(Claim $claim): JsonResponse
    {
        $claim->load(['patient.insurances.carrier', 'insurance.carrier', 'lineItems.cdtCode', 'attachments', 'renderingProvider', 'billingProvider', 'location']);
        return $this->success($claim);
    }

    /**
     * Generate a draft claim from an appointment's procedures.
     */
    public function generateFromAppointment(Request $request, Appointment $appointment): JsonResponse
    {
        // Find completed procedures for this appointment
        // Assuming procedures are linked via TreatmentPlanProcedure or a separate AppointmentProcedure table
        // For this implementation, we'll look for TreatmentPlanProcedures linked to this appointment
        $procedures = TreatmentPlanProcedure::where('appointment_id', $appointment->id)
            ->where('status', 'completed')
            ->get();

        if ($procedures->isEmpty()) {
            return $this->error('No completed procedures found for this appointment.', 422);
        }

        $patient = $appointment->patient;
        $insurance = $patient->primaryInsurance();

        if (!$insurance) {
            return $this->error('Patient has no primary insurance on file.', 422);
        }

        $claim = DB::transaction(function () use ($appointment, $procedures, $patient, $insurance) {
            $claim = Claim::create([
                'tenant_id' => $appointment->tenant_id,
                'claim_number' => 'CLM-' . strtoupper(uniqid()),
                'patient_id' => $patient->id,
                'insurance_id' => $insurance->id,
                'appointment_id' => $appointment->id,
                'rendering_provider_id' => $appointment->provider_id,
                'billing_provider_id' => $appointment->provider_id, // Default to same
                'location_id' => $appointment->location_id,
                'claim_type' => 'primary',
                'status' => 'draft',
                'total_billed' => $procedures->sum('fee'),
            ]);

            foreach ($procedures as $proc) {
                ClaimLineItem::create([
                    'claim_id' => $claim->id,
                    'treatment_plan_procedure_id' => $proc->id,
                    'tooth_number' => $proc->tooth_number,
                    'surfaces' => $proc->surfaces,
                    'cdt_code_id' => $proc->cdt_code_id,
                    'description' => $proc->procedure_name,
                    'fee_billed' => $proc->fee,
                    'status' => 'included',
                ]);
            }

            return $claim;
        });

        return $this->success($claim->load('lineItems'), 'Draft claim generated');
    }

    /**
     * Validate/Scrub a claim.
     */
    public function scrub(Claim $claim): JsonResponse
    {
        $errors = [];
        $claim->load(['patient', 'insurance', 'lineItems.cdtCode', 'renderingProvider']);

        // Basic validations
        if (!$claim->patient->date_of_birth) $errors[] = 'Patient Date of Birth is missing.';
        if (!$claim->insurance->subscriber_id) $errors[] = 'Subscriber ID is missing.';
        if (!$claim->renderingProvider->npi) $errors[] = 'Rendering Provider NPI is missing.';

        // Procedure specific scrubbing
        foreach ($claim->lineItems as $item) {
            $code = $item->cdtCode->code;
            
            // Example: Scaling/Root Planing (D4341) requires Perio Chart and X-rays
            if ($code === 'D4341' || $code === 'D4342') {
                $hasXray = $claim->attachments()->where('attachment_type', 'xray')->exists();
                $hasPerio = $claim->attachments()->where('attachment_type', 'perio_chart')->exists();
                if (!$hasXray) $errors[] = "Procedure $code requires an X-ray attachment.";
                if (!$hasPerio) $errors[] = "Procedure $code requires a Perio Chart attachment.";
            }

            // Example: Crowns (D2740) require narrative and X-ray
            if (str_starts_with($code, 'D27')) {
                $hasNarrative = $claim->attachments()->where('attachment_type', 'narrative')->exists();
                if (!$hasNarrative) $errors[] = "Crown procedure $code requires a clinical narrative.";
            }
        }

        $claim->update([
            'is_scrubbed' => true,
            'scrubbing_errors' => $errors,
        ]);

        return $this->success([
            'valid' => empty($errors),
            'errors' => $errors
        ]);
    }

    /**
     * Submit claim to clearinghouse.
     */
    public function submit(Claim $claim): JsonResponse
    {
        if (!$claim->is_scrubbed || !empty($claim->scrubbing_errors)) {
            return $this->error('Claim must be scrubbed and error-free before submission.', 422);
        }

        // Simulate submission to clearinghouse
        $claim->update([
            'status' => 'submitted',
            'submitted_at' => now(),
            'clearinghouse_claim_id' => 'EDI-' . rand(100000, 999999),
        ]);

        return $this->success($claim, 'Claim submitted to clearinghouse');
    }

    /**
     * Preview ADA Claim Form.
     */
    public function preview(Claim $claim)
    {
        $claim->load(['patient', 'insurance.carrier', 'lineItems.cdtCode', 'renderingProvider', 'billingProvider', 'location']);
        
        $pdf = Pdf::loadView('pdf.claim_form', ['claim' => $claim]);
        
        return $pdf->stream("claim_{$claim->claim_number}.pdf");
    }

    /**
     * Add attachment to claim.
     */
    public function addAttachment(Request $request, Claim $claim): JsonResponse
    {
        $request->validate([
            'type' => 'required|in:xray,photo,perio_chart,narrative,other',
            'file' => 'required|file|max:5120', // 5MB
            'narrative_text' => 'nullable|string',
        ]);

        if ($request->type === 'narrative' && $request->narrative_text) {
            // Create a text file for the narrative
            $fileName = "narrative_" . time() . ".txt";
            $filePath = "claims/attachments/" . $fileName;
            Storage::put($filePath, $request->narrative_text);
        } else {
            $file = $request->file('file');
            $fileName = $file->getClientOriginalName();
            $filePath = $file->store('claims/attachments');
        }

        $attachment = ClaimAttachment::create([
            'claim_id' => $claim->id,
            'attachment_type' => $request->type,
            'file_path' => $filePath,
            'file_name' => $fileName,
            'file_size_bytes' => Storage::size($filePath),
            'created_by' => $request->user()->id,
        ]);

        return $this->success($attachment, 'Attachment added to claim');
    }

    /**
     * Generate secondary claim.
     */
    public function generateSecondary(Claim $primaryClaim): JsonResponse
    {
        if ($primaryClaim->claim_type !== 'primary' || $primaryClaim->status !== 'paid') {
            return $this->error('Secondary claims can only be generated from paid primary claims.', 422);
        }

        $patient = $primaryClaim->patient;
        $secondaryInsurance = $patient->insurances()->where('sequence', 2)->first();

        if (!$secondaryInsurance) {
            return $this->error('Patient has no secondary insurance on file.', 422);
        }

        $secondaryClaim = DB::transaction(function () use ($primaryClaim, $secondaryInsurance) {
            $newClaim = $primaryClaim->replicate(['id', 'created_at', 'updated_at', 'submitted_at', 'paid_at', 'total_paid', 'era_id', 'clearinghouse_claim_id']);
            $newClaim->claim_number = $primaryClaim->claim_number . '-S';
            $newClaim->insurance_id = $secondaryInsurance->id;
            $newClaim->claim_type = 'secondary';
            $newClaim->status = 'draft';
            $newClaim->is_scrubbed = false;
            $newClaim->save();

            foreach ($primaryClaim->lineItems as $item) {
                $newItem = $item->replicate(['id', 'created_at', 'updated_at', 'insurance_paid', 'patient_portion', 'adjustment', 'adjustment_type']);
                $newItem->claim_id = $newClaim->id;
                $newItem->status = 'included';
                $newItem->save();
            }

            $primaryClaim->update(['secondary_claim_id' => $newClaim->id]);

            return $newClaim;
        });

        return $this->success($secondaryClaim, 'Secondary claim generated');
    }
}
