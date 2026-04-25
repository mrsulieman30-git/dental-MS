<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponseTrait;
use App\Models\InsuranceCarrier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InsuranceCarrierController extends Controller
{
    use ApiResponseTrait;

    public function index(Request $request): JsonResponse
    {
        $query = InsuranceCarrier::query()->withCount('patientInsurances');
        if ($request->has('active_only')) $query->where('is_active', true);
        if ($request->has('search')) $query->where('name', 'like', '%' . $request->search . '%');
        return $this->success($query->orderBy('name')->get());
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|array',
            'phone' => 'nullable|string|max:20',
            'fax' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'website' => 'nullable|string|max:255',
            'payer_id' => 'nullable|string|max:50',
            'notes' => 'nullable|string',
        ]);
        $carrier = InsuranceCarrier::create(array_merge($validated, ['tenant_id' => 1, 'is_active' => true]));
        return $this->success($carrier, 'Carrier created', 201);
    }

    public function update(Request $request, InsuranceCarrier $carrier): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'address' => 'nullable|array',
            'phone' => 'nullable|string|max:20',
            'fax' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'website' => 'nullable|string|max:255',
            'payer_id' => 'nullable|string|max:50',
            'notes' => 'nullable|string',
        ]);
        $carrier->update($validated);
        return $this->success($carrier->fresh(), 'Carrier updated');
    }

    public function deactivate(InsuranceCarrier $carrier): JsonResponse
    {
        $carrier->update(['is_active' => !$carrier->is_active]);
        return $this->success($carrier->fresh(), $carrier->is_active ? 'Activated' : 'Deactivated');
    }
}
