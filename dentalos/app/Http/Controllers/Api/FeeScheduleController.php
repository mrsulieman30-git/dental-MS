<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponseTrait;
use App\Models\FeeSchedule;
use App\Models\FeeScheduleItem;
use App\Models\CdtCode;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FeeScheduleController extends Controller
{
    use ApiResponseTrait;

    public function index(): JsonResponse
    {
        $schedules = FeeSchedule::with('carrier')
            ->withCount('items')
            ->orderBy('name')
            ->get();
        return $this->success($schedules);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'carrier_id' => 'nullable|exists:insurance_carriers,id',
            'type' => 'required|in:ucr,insurance_contracted,medicaid,custom',
            'effective_date' => 'nullable|date',
            'expiry_date' => 'nullable|date',
        ]);
        $schedule = FeeSchedule::create(array_merge($validated, [
            'tenant_id' => 1,
            'is_active' => true,
        ]));
        return $this->success($schedule, 'Fee schedule created', 201);
    }

    public function update(Request $request, FeeSchedule $schedule): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'carrier_id' => 'nullable|exists:insurance_carriers,id',
            'type' => 'sometimes|in:ucr,insurance_contracted,medicaid,custom',
            'effective_date' => 'nullable|date',
            'expiry_date' => 'nullable|date',
            'is_active' => 'sometimes|boolean',
        ]);
        $schedule->update($validated);
        return $this->success($schedule->fresh(), 'Fee schedule updated');
    }

    public function destroy(FeeSchedule $schedule): JsonResponse
    {
        $schedule->delete();
        return $this->success(null, 'Fee schedule deleted');
    }

    public function items(FeeSchedule $schedule): JsonResponse
    {
        return $this->success(
            $schedule->items()->with('cdtCode')->orderBy('id')->get()
        );
    }

    public function addItem(Request $request, FeeSchedule $schedule): JsonResponse
    {
        $validated = $request->validate([
            'cdt_code_id' => 'required|exists:cdt_codes,id',
            'fee' => 'required|numeric|min:0',
        ]);
        $item = FeeScheduleItem::updateOrCreate(
            ['fee_schedule_id' => $schedule->id, 'cdt_code_id' => $validated['cdt_code_id']],
            ['fee' => $validated['fee']]
        );
        return $this->success($item->load('cdtCode'), 'Item added');
    }

    public function importCsv(Request $request, FeeSchedule $schedule): JsonResponse
    {
        $request->validate(['items' => 'required|array']);
        $imported = 0;
        DB::transaction(function () use ($request, $schedule, &$imported) {
            foreach ($request->items as $row) {
                $cdtCode = CdtCode::where('code', $row['code'] ?? '')->first();
                if (!$cdtCode) continue;
                FeeScheduleItem::updateOrCreate(
                    ['fee_schedule_id' => $schedule->id, 'cdt_code_id' => $cdtCode->id],
                    ['fee' => $row['fee'] ?? 0]
                );
                $imported++;
            }
        });
        return $this->success(['imported' => $imported], "{$imported} items imported");
    }

    public function bulkAdjust(Request $request, FeeSchedule $schedule): JsonResponse
    {
        $request->validate(['percent' => 'required|numeric|min:-100|max:1000']);
        $multiplier = 1 + ($request->percent / 100);
        $schedule->items()->update([
            'fee' => DB::raw("ROUND(fee * {$multiplier}, 2)")
        ]);
        return $this->success(null, "Fees adjusted by {$request->percent}%");
    }
}
