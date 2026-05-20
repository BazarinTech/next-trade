<?php

namespace App\Http\Middleware;

use App\Services\PermissionService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasPermission
{
    public function __construct(private PermissionService $permissions) {}

    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $user = auth()->user();

        if (!$user || !$user->is_admin) {
            return $this->deny($request);
        }

        if (!$this->permissions->userHasPermission($user, $permission)) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Insufficient permissions.'], 403);
            }
            return redirect()->route('admin.dashboard')
                ->with('error', 'You do not have permission to access that section.');
        }

        return $next($request);
    }

    private function deny(Request $request): Response
    {
        if ($request->expectsJson()) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }
        return redirect()->route('dashboard')->with('error', 'Access denied.');
    }
}
