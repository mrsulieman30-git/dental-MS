<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePatientRequest;
use App\Http\Resources\PatientResource;
use App\Http\Resources\PatientDetailResource;
use App\Http\Traits\ApiResponseTrait;
use App\Models\Patient;
use App\Events\PatientViewed;
use App\Services\AuditService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

class PatientController extends Controller
{
    use ApiResponseTrait;

    public function __construct(protected AuditService $audit) {}

    public function index(Request $request): JsonResponse
    {
        Gate::authorize('viewAny', Patient::class);

        $patients = Patient::query()
            ->when($request->search, function ($query, $search) {
                $query->where('first_name', 'like', "%{$search}%")
                      ->orWhere('last_name', 'like', "%{$search}%")
                      ->orWhere('patient_number', 'like', "%{$search}%")
                      ->orWhere('phone_number', 'like', "%{$search}%"); // Added phone
            })
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->location_id, fn($q) => $q->where('primary_location_id', $request->location_id))
            ->paginate((int)($request->per_page ?? 15));

        return $this->paginated($patients); // Traits handles Resource wrapping if needed, but here simple paginator
    }

    public function store(StorePatientRequest $request): JsonResponse
    {
        Gate::authorize('create', Patient::class);

        $patient = Patient::create($request->validated());

        $this->audit->log('created', 'Patient', $patient->id, null, $patient->toArray());

        return $this->success(new PatientResource($patient), 'Patient created successfully', 201);
    }

    public function show(Patient $patient): JsonResponse
    {
        Gate::authorize('view', $patient);
        event(new PatientViewed($patient));
        return $this->success(new PatientDetailResource($patient));
    }

    public function update(Request $request, Patient $patient): JsonResponse
    {
        Gate::authorize('update', $patient);

        $oldValue = $patient->toArray();
        $patient->update($request->all());
        
        $this->audit->log('updated', 'Patient', $patient->id, $oldValue, $patient->toArray());

        return $this->success(new PatientResource($patient), 'Patient updated successfully');
    }

    public function destroy(Patient $patient): JsonResponse
    {
        Gate::authorize('delete', $patient);
        
        $this->audit->log('deleted', 'Patient', $patient->id, $patient->toArray(), null);
        
        $patient->delete();
        return $this->success(null, 'Patient deleted successfully');
    }

    public function timeline(Patient $patient): JsonResponse
    {
        Gate::authorize('view', $patient);

        // This is a complex query combining multiple models
        // In a real app, use a dedicated service or a view/union
        $timeline = collect();

        $patient->appointments->each(fn($a) => $timeline->push(['type' => 'appointment', 'date' => $a->start_time, 'data' => $a]));
        $patient->clinicalNotes->each(fn($n) => $timeline->push(['type' => 'note', 'date' => $n->created_at, 'data' => $n]));
        
        return $this->success($timeline->sortByDesc('date')->values());
    }

    public function checkDuplicate(Request $request): JsonResponse
    {
        $request->validate([
            'first_name' => 'required',
            'last_name' => 'required',
            'date_of_birth' => 'required|date',
        ]);

        $matches = Patient::where('first_name', $request->first_name)
            ->where('last_name', $request->last_name)
            ->where('date_of_birth', $request->date_of_birth)
            ->get();

        return $this->success(PatientResource::collection($matches));
    }

    public function sendPortalInvite(Patient $patient): JsonResponse
    {
        if ($patient->has_portal_account) {
            return $this->error('Patient already has a portal account');
        }

        $token = Str::random(64);
        // Store token in patient_invites table or cache
        $inviteUrl = URL::to('/portal/register?token=' . $token);

        // Dispatch email job here

        return $this->success(['invite_url' => $inviteUrl], 'Portal invitation sent');
    }
}
