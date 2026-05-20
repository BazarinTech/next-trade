<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class RateLimiterServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Login: 5 per minute per email+IP
        RateLimiter::for('login', function (Request $request) {
            return Limit::perMinute(5)->by(
                strtolower($request->input('email')) . '|' . $request->ip()
            );
        });

        // Register: 3 per minute per IP
        RateLimiter::for('register', function (Request $request) {
            return Limit::perMinute(3)->by($request->ip());
        });

        // Deposit initiation: 5 per minute per user
        RateLimiter::for('deposit', function (Request $request) {
            return Limit::perMinute(5)->by('deposit|' . ($request->user()?->id ?? $request->ip()));
        });

        // Deposit status refresh: 6 per minute per user
        RateLimiter::for('deposit-refresh', function (Request $request) {
            return Limit::perMinute(6)->by('deposit-refresh|' . ($request->user()?->id ?? $request->ip()));
        });

        // Trade placement: 20 per minute per user
        RateLimiter::for('trade', function (Request $request) {
            return Limit::perMinute(20)->by('trade|' . ($request->user()?->id ?? $request->ip()));
        });

        // Bot investment: 10 per minute per user
        RateLimiter::for('bot-invest', function (Request $request) {
            return Limit::perMinute(10)->by('bot|' . ($request->user()?->id ?? $request->ip()));
        });

        // Withdrawal request: 3 per minute per user
        RateLimiter::for('withdrawal', function (Request $request) {
            return Limit::perMinute(3)->by('withdrawal|' . ($request->user()?->id ?? $request->ip()));
        });

        // Admin sensitive actions: 30 per minute per admin
        RateLimiter::for('admin-action', function (Request $request) {
            return Limit::perMinute(30)->by('admin|' . ($request->user()?->id ?? $request->ip()));
        });
    }
}
