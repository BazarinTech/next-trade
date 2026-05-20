<?php

namespace App\Http\Middleware;

use App\Services\SettingsService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class MaintenanceMiddleware
{
    public function __construct(private SettingsService $settings) {}

    public function handle(Request $request, Closure $next): Response
    {
        if ($this->settings->boolean('maintenance_mode', false)) {
            $user = auth()->user();
            // Admins can still access the site
            if (!$user || !$user->is_admin) {
                if ($request->expectsJson()) {
                    return response()->json(['message' => 'Platform is under maintenance.'], 503);
                }
                return response()->view('maintenance', [], 503);
            }
        }
        return $next($request);
    }
}
