<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponseTrait;
use App\Models\ClinicalNote;
use App\Models\NoteTemplate;
use App\Models\Patient;
use App\Services\AuditService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ClinicalNoteController extends Controller
{
    use ApiResponseTrait;

    public function __construct(protected AuditService $audit) {}

    public function index(Patient $patient): JsonResponse
    {
        Gate::authorize('view', $patient);

        $notes = $patient->clinicalNotes()
            ->with(['provider:id,first_name,last_name', 'template:id,name'])
            ->latest()
            ->get();

        return $this->success($notes);
    }

    public function store(Request $request, Patient $patient): JsonResponse
    {
        Gate::authorize('view', $patient);

        $validated = $request->validate([
            'appointment_id' => 'nullable|exists:appointments,id',
            'note_type' => 'required|in:soap,progress,consult,referral,phone,general',
            'template_id' => 'nullable|exists:note_templates,id',
            'full_note_text' => 'nullable|string',
            'subjective' => 'nullable|string',
            'objective' => 'nullable|string',
            'assessment' => 'nullable|string',
            'plan' => 'nullable|string',
        ]);

        if ($this->isEmptyNotePayload($validated)) {
            return $this->error('A note must include content before it can be saved.', [], 422);
        }

        $note = ClinicalNote::create($validated + [
            'patient_id' => $patient->id,
            'provider_id' => $request->user()->id,
            'version' => 1,
        ])->load(['provider:id,first_name,last_name', 'template:id,name']);

        $this->audit->log('created', 'ClinicalNote', $note->id);

        return $this->success($note, 'Note created', 201);
    }

    public function update(Request $request, ClinicalNote $note): JsonResponse
    {
        Gate::authorize('view', $note->patient);

        if ($note->is_locked) {
            return $this->error('Cannot update a locked note. Use amendments instead.', [], 403);
        }

        $validated = $request->validate([
            'appointment_id' => 'sometimes|nullable|exists:appointments,id',
            'note_type' => 'sometimes|in:soap,progress,consult,referral,phone,general',
            'template_id' => 'sometimes|nullable|exists:note_templates,id',
            'full_note_text' => 'sometimes|nullable|string',
            'subjective' => 'sometimes|nullable|string',
            'objective' => 'sometimes|nullable|string',
            'assessment' => 'sometimes|nullable|string',
            'plan' => 'sometimes|nullable|string',
        ]);

        $note->update($validated);

        return $this->success(
            $note->fresh(['provider:id,first_name,last_name', 'template:id,name']),
            'Note updated'
        );
    }

    public function sign(ClinicalNote $note, Request $request): JsonResponse
    {
        Gate::authorize('view', $note->patient);

        $note->update([
            'is_signed' => true,
            'signed_at' => now(),
            'provider_id' => $request->user()->id,
        ]);

        $this->audit->log('signed', 'ClinicalNote', $note->id);

        return $this->success($note->fresh(['provider:id,first_name,last_name']), 'Note signed');
    }

    public function lock(ClinicalNote $note, Request $request): JsonResponse
    {
        Gate::authorize('view', $note->patient);

        $note->update([
            'is_locked' => true,
            'locked_at' => now(),
            'locked_by' => $request->user()->id,
        ]);

        $this->audit->log('locked', 'ClinicalNote', $note->id);

        return $this->success($note->fresh(['provider:id,first_name,last_name']), 'Note locked');
    }

    public function amend(Request $request, ClinicalNote $note): JsonResponse
    {
        Gate::authorize('view', $note->patient);

        if (!$note->is_locked) {
            return $this->error('Only locked notes can be amended.', [], 422);
        }

        $request->validate(['amendment_notes' => 'required|string']);

        $providerName = trim($request->user()->first_name . ' ' . $request->user()->last_name);
        $timestamp = now()->format('Y-m-d H:i:s');
        $entry = sprintf('[%s] %s: %s', $timestamp, $providerName ?: 'Provider', $request->amendment_notes);

        $note->update([
            'is_amended' => true,
            'amendment_notes' => trim(($note->amendment_notes ? $note->amendment_notes . PHP_EOL : '') . $entry),
        ]);

        $this->audit->log('amended', 'ClinicalNote', $note->id, null, ['amendment' => $request->amendment_notes]);

        return $this->success($note->fresh(['provider:id,first_name,last_name']), 'Amendment added');
    }

    public function templates(Request $request): JsonResponse
    {
        $templates = NoteTemplate::query()
            ->where('tenant_id', $request->user()->tenant_id)
            ->where('is_active', true)
            ->when(
                $request->filled('note_type'),
                fn ($query) => $query->where('note_type', $request->string('note_type')->toString())
            )
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return $this->success($templates);
    }

    private function isEmptyNotePayload(array $payload): bool
    {
        return collect([
            $payload['full_note_text'] ?? null,
            $payload['subjective'] ?? null,
            $payload['objective'] ?? null,
            $payload['assessment'] ?? null,
            $payload['plan'] ?? null,
        ])->every(function ($value) {
            if (!is_string($value)) {
                return blank($value);
            }

            return blank(trim(strip_tags($value)));
        });
    }
}
