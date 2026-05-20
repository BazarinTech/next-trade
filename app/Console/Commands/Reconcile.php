<?php

namespace App\Console\Commands;

use App\Models\Transaction;
use App\Models\Wallet;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Reconcile wallet balances against transaction history.
 * Reports mismatches. Use --fix to auto-correct.
 *
 * NEVER deletes financial records.
 */
class Reconcile extends Command
{
    protected $signature   = 'nexttrade:reconcile {--fix : Apply corrections to mismatched wallets}';
    protected $description = 'Check wallet balances against transaction sums, report/fix mismatches';

    public function handle(): int
    {
        $fix      = $this->option('fix');
        $wallets  = Wallet::with('user')->get();
        $mismatches = [];

        $this->info("Reconciling {$wallets->count()} wallets...");

        foreach ($wallets as $wallet) {
            $creditSum = (float) Transaction::where('wallet_id', $wallet->id)
                ->where('status', 'successful')
                ->whereIn('type', ['deposit', 'trade_profit', 'bot_profit', 'demo_reset', 'manual_credit'])
                ->sum('amount');

            $debitSum = (float) Transaction::where('wallet_id', $wallet->id)
                ->where('status', 'successful')
                ->whereIn('type', ['withdrawal', 'trade_stake', 'bot_investment', 'fee'])
                ->sum('amount');

            $lockedSum = (float) Transaction::where('wallet_id', $wallet->id)
                ->where('status', 'pending')
                ->whereIn('type', ['withdrawal'])
                ->sum('amount');

            $expectedBalance = round($creditSum - $debitSum, 8);
            $actualBalance   = round((float) $wallet->balance, 8);
            $diff            = round($expectedBalance - $actualBalance, 8);

            if (abs($diff) > 0.001) {
                $mismatches[] = [
                    'wallet_id'   => $wallet->id,
                    'user'        => $wallet->user?->email ?? 'unknown',
                    'type'        => $wallet->type,
                    'expected'    => $expectedBalance,
                    'actual'      => $actualBalance,
                    'diff'        => $diff,
                ];
            }
        }

        if (empty($mismatches)) {
            $this->info('✓ All wallet balances match transaction history.');
        } else {
            $this->warn(count($mismatches) . ' mismatch(es) found:');
            $this->table(
                ['Wallet', 'User', 'Type', 'Expected', 'Actual', 'Diff'],
                array_map(fn($m) => [
                    $m['wallet_id'], $m['user'], $m['type'],
                    '$' . number_format($m['expected'], 4),
                    '$' . number_format($m['actual'], 4),
                    ($m['diff'] > 0 ? '+' : '') . '$' . number_format($m['diff'], 4),
                ], $mismatches)
            );

            if ($fix) {
                $this->warn('Applying --fix corrections...');
                foreach ($mismatches as $m) {
                    Wallet::where('id', $m['wallet_id'])->update(['balance' => $m['expected']]);
                    $this->line("Fixed wallet #{$m['wallet_id']} ({$m['user']})");
                }
                $this->info('Corrections applied.');
            } else {
                $this->line('Run with --fix to apply corrections.');
            }
        }

        $report = [
            'run_at'       => now()->toDateTimeString(),
            'wallets'      => $wallets->count(),
            'mismatches'   => count($mismatches),
            'details'      => $mismatches,
            'fixed'        => $fix,
        ];

        $logPath = storage_path('logs/reconciliation_' . now()->format('Ymd_His') . '.json');
        file_put_contents($logPath, json_encode($report, JSON_PRETTY_PRINT));
        Log::info('nexttrade:reconcile', ['mismatches' => count($mismatches), 'fixed' => $fix]);
        $this->line("Report saved to: {$logPath}");

        return self::SUCCESS;
    }
}
