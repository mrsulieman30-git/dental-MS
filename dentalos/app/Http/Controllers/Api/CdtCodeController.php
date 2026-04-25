<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponseTrait;
use App\Models\CdtCode;
use App\Models\FeeScheduleItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CdtCodeController extends Controller
{
    use ApiResponseTrait;

    public function search(Request $request): JsonResponse
    {
        $query = $request->get('q', '');
        $scheduleId = $request->get('fee_schedule_id');

        $codes = CdtCode::where('is_active', true)
            ->where(function ($q) use ($query) {
                $q->where('code', 'like', "%{$query}%")
                  ->orWhere('description', 'like', "%{$query}%")
                  ->orWhere('short_description', 'like', "%{$query}%");
            })
            ->limit(20)
            ->get()
            ->map(function ($code) use ($scheduleId) {
                $fee = null;
                if ($scheduleId) {
                    $item = FeeScheduleItem::where('fee_schedule_id', $scheduleId)
                        ->where('cdt_code_id', $code->id)
                        ->first();
                    $fee = $item?->fee;
                }
                return [
                    'id' => $code->id,
                    'code' => $code->code,
                    'description' => $code->description,
                    'short_description' => $code->short_description,
                    'category' => $code->category,
                    'default_fee' => $fee,
                ];
            });

        return $this->success($codes);
    }
}
