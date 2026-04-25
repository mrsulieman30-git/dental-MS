<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAppointmentRequest;
use App\Http\Traits\ApiResponseTrait;
use App\Models\Appointment;
use App\Models\Waitlist;
use App\Services\AuditService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Carbon\Carbon;

class AppointmentController extends Controller
{
    use ApiResponseTrait;

    public function __construct(protected AuditService $audit) {}

    public function index(Request $request): JsonResponse
    {
        Gate::authorize('viewAny', Appointment::class);

        $appointments = Appointment::query()
            ->where('tenant_id', $request->user()->tenant_id)
            ->when($request->location_id, fn($q) => $q->where('location_id', $request->location_id))
            ->when($request->provider_id, fn($q) => $q->where('provider_id', $request->provider_id))
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->date_from && $request->date_to, fn($q) => $q->whereBetween('start_time', [$request->date_from, $request->date_to]))
            ->with(['patient', 'provider', 'appointmentType', 'operatory'])
            ->get();

        return $this->success($appointments);
    }

    public function store(StoreAppointmentRequest $request): JsonResponse
    {
        Gate::authorize('create', Appointment::class);

        // Double-booking check
        $conflicts = Appointment::where('operatory_id', $request->operatory_id)
            ->where('start_time', '<', Carbon::parse($request->start_time)->addMinutes($request->duration_minutes))
            ->where('end_time', '>', $request->start_time)
            ->exists();

        if ($conflicts) {
            return $this->error('Double-booking detected for this operatory', [], 422);
        }

        $appointment = Appointment::create($request->validated() + [
            'tenant_id' => $request->user()->tenant_id,
            'end_time' => Carbon::parse($request->start_time)->addMinutes($request->duration_minutes),
            'status' => 'scheduled',
            'created_by' => $request->user()->id
        ]);

        $this->audit->log('scheduled', 'Appointment', $appointment->id, null, $appointment->toArray());

        return $this->success($appointment, 'Appointment scheduled successfully', 201);
    }

    public function show(Appointment $appointment): JsonResponse
    {
        Gate::authorize('view', $appointment);
        return $this->success($appointment->load(['patient.alerts', 'patient.insurance', 'appointmentType', 'operatory']));
    }

    public function update(Request $request, Appointment $appointment): JsonResponse
    {
        Gate::authorize('update', $appointment);
        $appointment->update($request->all());
        return $this->success($appointment, 'Appointment updated');
    }

    public function updateStatus(Request $request, Appointment $appointment): JsonResponse
    {
        Gate::authorize('update', $appointment);
        $request->validate(['status' => 'required']);

        $oldStatus = $appointment->status;
        $appointment->update(['status' => $request->status]);

        if ($request->status === 'completed') {
            $appointment->update(['completed_at' => now()]);
            // Logic to auto-create ledger charges would go here
        }

        $this->audit->log('status_updated', 'Appointment', $appointment->id, ['status' => $oldStatus], ['status' => $request->status]);

        return $this->success($appointment, 'Status updated');
    }

    public function getSchedule(Request $request): JsonResponse
    {
        // Format for FullCalendar
        $appointments = $this->index($request)->getData()->data;
        $formatted = collect($appointments)->map(fn($a) => [
            'id' => $a->id,
            'title' => $a->patient->full_name . ' (' . $a->appointment_type->name . ')',
            'start' => $a->start_time,
            'end' => $a->end_time,
            'backgroundColor' => $a->appointment_type->color,
        ]);

        return $this->success($formatted);
    }

    public function checkIn(Appointment $appointment): JsonResponse
    {
        Gate::authorize('update', $appointment);
        $appointment->update(['checked_in_at' => now(), 'status' => 'checked_in']);
        return $this->success($appointment, 'Patient checked in');
    }

    public function waitlistIndex(Request $request): JsonResponse
    {
        $list = Waitlist::where('tenant_id', $request->user()->tenant_id)->with(['patient', 'appointmentType'])->get();
        return $this->success($list);
    }

    public function waitlistStore(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'appointment_type_id' => 'required|exists:appointment_types,id',
            'preferred_days' => 'nullable|array',
        ]);

        $entry = Waitlist::create($validated + ['tenant_id' => $request->user()->tenant_id, 'status' => 'waiting', 'added_at' => now()]);
        return $this->success($entry, 'Added to waitlist', 201);
    }
}
