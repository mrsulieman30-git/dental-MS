<?php

declare(strict_types=1);

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Laravel\Sanctum\PersonalAccessToken;

class CleanupExpiredSessions implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        // Remove tokens that haven't been used in 30 days
        PersonalAccessToken::where('last_used_at', '<', now()->subDays(30))
            ->orWhere(function ($query) {
                $query->whereNull('last_used_at')
                      ->where('created_at', '<', now()->subDays(30));
            })
            ->delete();
    }
}
