<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Platform health check.
 * Checks env, DB, storage, queue, and app configuration.
 */
class HealthCheck extends Command
{
    protected $signature   = 'nexttrade:health';
    protected $description = 'Check platform health: DB, env, storage, scheduler, PalPluss config';

    public function handle(): int
    {
        $this->info('=== Next Trade Health Check ===');
        $allGood = true;

        $allGood = $this->check('App key set',    fn() => strlen(config('app.key')) > 0) && $allGood;
        $allGood = $this->check('Environment',    fn() => config('app.env'), info: true) && $allGood;
        $allGood = $this->check('Debug mode',     fn() => !config('app.debug') ? '✓ Off (production)' : '⚠ On (dev mode)') && $allGood;

        // Database
        try {
            DB::connection()->getPdo();
            $this->check('Database connection', fn() => true);
        } catch (\Exception $e) {
            $this->error("✗ Database connection: {$e->getMessage()}");
            $allGood = false;
        }

        // Required env variables
        $required = [
            'APP_KEY', 'DB_HOST', 'DB_DATABASE', 'DB_USERNAME',
        ];
        foreach ($required as $env) {
            $allGood = $this->check("ENV: {$env}", fn() => !empty(env($env))) && $allGood;
        }

        // PalPluss config
        $allGood = $this->check('PalPluss shortcode', fn() => !empty(env('PALPLUSS_SHORTCODE'))) && $allGood;
        $allGood = $this->check('PalPluss secret',    fn() => !empty(env('PALPLUSS_SECRET'))) && $allGood;
        $allGood = $this->check('USD/KES rate env',   fn() => !empty(env('USD_KES_RATE'))) && $allGood;

        // Storage
        $allGood = $this->check('Storage symlink', fn() => file_exists(public_path('storage'))) && $allGood;
        $allGood = $this->check('Logs writable',   fn() => is_writable(storage_path('logs'))) && $allGood;

        // Queue
        $queueConn = config('queue.default');
        $this->check("Queue driver: {$queueConn}", fn() => true, info: true);

        // Scheduler note
        $this->line('');
        $this->line('<fg=yellow>Scheduler cron (add to crontab):</>');
        $this->line('  * * * * * php ' . base_path('artisan') . ' schedule:run >> /dev/null 2>&1');

        $this->line('');
        if ($allGood) {
            $this->info('✓ All checks passed.');
        } else {
            $this->warn('⚠ Some checks failed. Review above.');
        }

        return $allGood ? self::SUCCESS : self::FAILURE;
    }

    private function check(string $label, callable $check, bool $info = false): bool
    {
        try {
            $result = $check();
            if ($result === false) {
                $this->error("  ✗ {$label}");
                return false;
            }
            $display = is_string($result) ? $result : '✓';
            if ($info) {
                $this->line("  ℹ {$label}: {$display}");
            } else {
                $this->line("  <fg=green>✓</> {$label}: {$display}");
            }
            return true;
        } catch (\Exception $e) {
            $this->error("  ✗ {$label}: {$e->getMessage()}");
            return false;
        }
    }
}
