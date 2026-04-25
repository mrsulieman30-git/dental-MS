<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class HealthStatusService
{
    public function check(): array
    {
        $checks = [
            'database' => $this->checkDatabase(),
            'redis' => $this->checkRedis(),
            'storage' => $this->checkStorage(),
        ];

        $allHealthy = !in_array(false, array_column($checks, 'healthy'));

        return [
            'status' => $allHealthy ? 'healthy' : 'degraded',
            'timestamp' => now()->toIso8601String(),
            'services' => $checks,
        ];
    }

    private function checkDatabase(): array
    {
        try {
            DB::connection()->getPdo();
            return ['healthy' => true];
        } catch (\Exception $e) {
            return ['healthy' => false, 'message' => 'Database connection failed'];
        }
    }

    private function checkRedis(): array
    {
        try {
            Redis::connection()->ping();
            return ['healthy' => true];
        } catch (\Exception $e) {
            return ['healthy' => false, 'message' => 'Redis connection failed'];
        }
    }

    private function checkStorage(): array
    {
        return ['healthy' => is_writable(storage_path()), 'message' => is_writable(storage_path()) ? null : 'Storage is not writable'];
    }
}
