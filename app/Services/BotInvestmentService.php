<?php

namespace App\Services;

use App\Models\BotEarning;
use App\Models\BotInvestment;
use App\Models\BotPlan;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class BotInvestmentService
{
    public function __construct(
        private WalletService       $walletService,
        private SettingsService     $settings,
        private NotificationService $notifier
    ) {}

    public function getActivePlans(): Collection
    {
        return BotPlan::where('status', 'active')
            ->orderBy('sort_order')
            ->orderBy('daily_roi_percent')
            ->get();
    }

    public function invest(User $user, BotPlan $plan, float $amount, string $walletType): BotInvestment
    {
        if (!$this->settings->boolean('bot_investments_enabled', true)) {
            throw new RuntimeException('Bot investments are currently disabled. Please try again later.');
        }
        if (!$plan->isActive()) {
            throw new RuntimeException('This bot plan is not currently available.');
        }

        if ($amount < (float) $plan->min_investment) {
            throw new RuntimeException("Minimum investment for this plan is \${$plan->min_investment}.");
        }

        if ($plan->max_investment && $amount > (float) $plan->max_investment) {
            throw new RuntimeException("Maximum investment for this plan is \${$plan->max_investment}.");
        }

        $wallet = $this->walletService->getUserWallet($user, $walletType);

        if ($wallet->isFrozen()) {
            throw new RuntimeException('Your wallet is frozen. Please contact support.');
        }

        return DB::transaction(function () use ($user, $plan, $amount, $walletType, $wallet) {
            $startsAt = now();
            $endsAt   = $plan->duration_days ? $startsAt->copy()->addDays($plan->duration_days) : null;

            $this->walletService->debit(
                $wallet,
                $amount,
                'bot_investment',
                "Bot Investment: {$plan->name}",
                ['bot_plan_id' => $plan->id, 'bot_plan_name' => $plan->name]
            );

            return BotInvestment::create([
                'user_id'           => $user->id,
                'wallet_id'         => $wallet->id,
                'bot_plan_id'       => $plan->id,
                'wallet_type'       => $walletType,
                'principal_amount'  => $amount,
                'daily_roi_percent' => $plan->daily_roi_percent,
                'total_earned'      => 0,
                'total_withdrawn'   => 0,
                'started_at'        => $startsAt,
                'ends_at'           => $endsAt,
                'status'            => 'active',
                'metadata'          => ['plan_name' => $plan->name, 'invested_at' => $startsAt->toISOString()],
            ]);
        });
    }

    public function calculateDailyEarning(BotInvestment $investment): float
    {
        return round(
            (float) $investment->principal_amount * (float) $investment->daily_roi_percent / 100,
            8
        );
    }

    public function processDailyEarnings(): array
    {
        $processed = 0;
        $skipped   = 0;
        $completed = 0;
        $failed    = 0;

        $investments = BotInvestment::where('status', 'active')
            ->with(['wallet', 'botPlan'])
            ->get();

        foreach ($investments as $investment) {
            // Mark expired investments as completed first
            if ($investment->isExpired()) {
                $this->completeInvestment($investment);
                $completed++;
                continue;
            }

            // Skip if already earned today
            if ($investment->hasEarnedToday()) {
                $skipped++;
                continue;
            }

            try {
                $earning = $this->createPendingEarning($investment);
                $this->creditEarning($earning);
                $processed++;
            } catch (\Throwable $e) {
                Log::error("BotEarning failed for investment #{$investment->id}: " . $e->getMessage());
                $failed++;
            }
        }

        return compact('processed', 'skipped', 'completed', 'failed');
    }

    public function creditEarning(BotEarning $earning): void
    {
        if ($earning->status !== 'pending') {
            return;
        }

        DB::transaction(function () use ($earning) {
            $investment = BotInvestment::lockForUpdate()->find($earning->bot_investment_id);

            if (!$investment || !$investment->isActive()) {
                $earning->update(['status' => 'failed']);
                return;
            }

            $wallet = $earning->wallet;

            $this->walletService->credit(
                $wallet,
                $earning->amount,
                'bot_profit',
                "Bot Earnings: {$investment->botPlan->name} ({$earning->roi_percent}% ROI)",
                [
                    'bot_investment_id' => $investment->id,
                    'bot_plan_id'       => $investment->bot_plan_id,
                    'earning_date'      => $earning->earning_date->toDateString(),
                ]
            );

            $earning->update(['status' => 'credited']);

            $investment->update([
                'total_earned'      => bcadd((string) $investment->total_earned, (string) $earning->amount, 8),
                'last_earning_at'   => now(),
            ]);

            $user = $wallet->user;
            $this->notifier->send($user, 'bot_earning', 'Bot Earnings Credited',
                "Your {$investment->botPlan->name} bot credited +\$" . number_format((float) $earning->amount, 2) . ' (' . $earning->roi_percent . '% ROI).',
                ['earning_id' => $earning->id, 'plan' => $investment->botPlan->name]);
        });
    }

    public function cancelInvestment(BotInvestment $investment): void
    {
        if (!$investment->isActive()) {
            throw new RuntimeException('Only active investments can be cancelled.');
        }

        $investment->update(['status' => 'cancelled']);
    }

    public function completeInvestment(BotInvestment $investment): void
    {
        $investment->update(['status' => 'completed']);
    }

    public function getUserPortfolio(User $user): array
    {
        $walletMode  = session('wallet_mode', 'demo');
        $wallet      = $this->walletService->getUserWallet($user, $walletMode);

        $investments = BotInvestment::where('user_id', $user->id)
            ->where('wallet_id', $wallet->id)
            ->with('botPlan')
            ->orderByDesc('created_at')
            ->get();

        $active    = $investments->where('status', 'active');
        $completed = $investments->whereIn('status', ['completed', 'cancelled']);

        $totalInvested  = $active->sum('principal_amount');
        $totalEarned    = $investments->sum('total_earned');
        $todayEarnings  = BotEarning::where('user_id', $user->id)
            ->where('wallet_id', $wallet->id)
            ->where('status', 'credited')
            ->whereDate('earning_date', today())
            ->sum('amount');

        return compact(
            'investments', 'active', 'completed',
            'totalInvested', 'totalEarned', 'todayEarnings', 'wallet', 'walletMode'
        );
    }

    private function createPendingEarning(BotInvestment $investment): BotEarning
    {
        $amount = $this->calculateDailyEarning($investment);

        return BotEarning::create([
            'user_id'           => $investment->user_id,
            'wallet_id'         => $investment->wallet_id,
            'bot_investment_id' => $investment->id,
            'bot_plan_id'       => $investment->bot_plan_id,
            'amount'            => $amount,
            'roi_percent'       => $investment->daily_roi_percent,
            'earning_date'      => today(),
            'status'            => 'pending',
        ]);
    }
}
