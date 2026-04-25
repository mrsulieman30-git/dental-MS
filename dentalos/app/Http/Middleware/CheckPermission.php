<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Services\PermissionService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $userPermissions = PermissionService::getRolePermissions($user->role);

        if (!in_array($permission, $userPermissions, true)) {
            return response()->json(['message' => 'Forbidden: Missing permission ' . $permission], 403);
        }

        return $next($request);
    }
}
