<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class AuditService
{
    /**
     * Log a general action.
     */
    public function log(
        string $action,
        string $resourceType,
        ?int $resourceId = null,
        mixed $oldValue = null,
        mixed $newValue = null,
        array $metadata = []
    ): void {
        AuditLog::create([
            'tenant_id' => Auth::user()?->tenant_id,
            'user_id' => Auth::id(),
            'action' => $action,
            'resource_type' => $resourceType,
            'resource_id' => $resourceId,
            'old_value' => $oldValue,
            'new_value' => $newValue,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
            'metadata' => $metadata,
            'created_at' => now(),
        ]);
    }

    /**
     * Log an authentication event.
     */
    public function logAuth(string $event, array $metadata = []): void
    {
        $this->log(
            action: 'auth.' . $event,
            resourceType: 'User',
            resourceId: Auth::id(),
            metadata: $metadata
        );
    }
}
