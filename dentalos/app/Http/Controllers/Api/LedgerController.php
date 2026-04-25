<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponseTrait;
use App\Models\Patient;
use App\Models\LedgerEntry;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class LedgerController extends Controller
{
    use ApiResponseTrait;

    public function index(Patient $patient): JsonResponse
    {
        Gate::authorize('view', $patient);
        
        $entries = $patient->ledgerEntries()
            ->with(['cdtCode', 'appointment', 'payment', 'claim'])
            ->orderBy('entry_date', 'desc')
            ->get();

        $balance = $entries->sum('amount');

        return $this->success([
            'entries' => $entries,
            'running_balance' => $balance
        ]);
    }
}
