<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Claim;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;

class SubmitClaimToClearinghouse implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $backoff = [60, 300, 600]; // 1m, 5m, 10m

    public function __construct(protected Claim $claim) {}

    public function handle(): void
    {
        $apiUrl = config('services.clearinghouse.url');
        $apiKey = config('services.clearinghouse.key');

        $response = Http::withToken($apiKey)->post($apiUrl . '/claims', [
            'claim_data' => $this->claim->toArray(), // simplified
        ]);

        if ($response->successful()) {
            $this->claim->update([
                'status' => 'submitted',
                'submitted_at' => now(),
                'clearinghouse_claim_id' => $response->json('id')
            ]);
        } else {
            $this->claim->update(['status' => 'failed', 'denial_reason' => $response->body()]);
            $this->fail(new \Exception('Claim submission failed: ' . $response->body()));
        }
    }
}
