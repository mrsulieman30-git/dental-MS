<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTenantAccess
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->user() || !$request->user()->tenant_id) {
            return response()->json(['message' => 'Unauthorized: No tenant context found.'], 401);
        }

        // We could also set a global config or app singleton for the current tenant here if needed
        // config(['app.current_tenant_id' => $request->user()->tenant_id]);

        return $next($request);
    }
}
