<?php

namespace App\Console\Commands;

use App\Models\PaymentDeposit;
use App\Models\PriceTick;
use App\Models\SystemSetting;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * Cleanup stale records safely — never deletes financial data.
 *
 * Schedule: * * * * * php /path/artisan schedule:run >> /dev/null 2>&1
 * Registered in console.php: Schedule::command('nexttrade:cleanup-pending')->daily();
 */
class CleanupPending extends Command
{
    protected $signature   = 'nexttrade:cleanup-pending {--dry-run : Preview changes without applying}';
    protected $description = 'Mark stale pending deposits as expired, clean old price ticks';

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');
        $this->info($dryRun ? '--- DRY RUN ---' : '--- Applying changes ---');

        // 1. Expire pending M-Pesa deposits older than 30 minutes
        $staleMpesa = PaymentDeposit::where('status', 'pending')
            ->where('method', 'mpesa_stk')
            ->where('created_at', '<', now()->subMinutes(30))
            ->get();

        $this->line("Stale M-Pesa deposits to expire: {$staleMpesa->count()}");

        if (!$dryRun && $staleMpesa->isNotEmpty()) {
            PaymentDeposit::whereIn('id', $staleMpesa->pluck('id'))
                ->update(['status' => 'failed', 'result_description' => 'Auto-expired after 30 minutes.']);
        }

        // 2. Expire pending USDT deposits older than 7 days with no admin action
        $staleUsdt = PaymentDeposit::where('status', 'pending')
            ->where('method', 'crypto_usdt_trc20')
            ->where('created_at', '<', now()->subDays(7))
            ->get();

        $this->line("Stale USDT deposits to expire: {$staleUsdt->count()}");

        if (!$dryRun && $staleUsdt->isNotEmpty()) {
            PaymentDeposit::whereIn('id', $staleUsdt->pluck('id'))
                ->update(['status' => 'failed', 'result_description' => 'Auto-expired after 7 days without admin review.']);
        }

        // 3. Clean old price ticks beyond configured retention days
        $retainDays = (int) SystemSetting::where('key', 'price_tick_retention_days')->value('value') ?: 3;
        $ticksBefore = now()->subDays($retainDays);
        $tickCount   = PriceTick::where('created_at', '<', $ticksBefore)->count();

        $this->line("Old price ticks to delete (older than {$retainDays}d): {$tickCount}");

        if (!$dryRun && $tickCount > 0) {
            PriceTick::where('created_at', '<', $ticksBefore)->delete();
        }

        SystemSetting::updateOrCreate(
            ['key' => 'last_cleanup_at'],
            ['value' => now()->toDateTimeString(), 'type' => 'string', 'group' => 'system']
        );

        $this->info('Cleanup complete.');
        Log::info('nexttrade:cleanup-pending', [
            'stale_mpesa' => $staleMpesa->count(),
            'stale_usdt'  => $staleUsdt->count(),
            'ticks_removed' => $dryRun ? 0 : $tickCount,
            'dry_run'     => $dryRun,
        ]);

        return self::SUCCESS;
    }
}
