<?php

namespace App\Services;

use App\Models\PaymentDeposit;
use App\Models\ReferralCommission;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ReferralService
{
    public function __construct(
        private WalletService   $walletService,
        private SettingsService $settings
    ) {}

    /**
     * Process a referral commission for a completed deposit.
     * Safe to call multiple times — idempotent via unique deposit_id constraint.
     */
    public function processDepositCommission(PaymentDeposit $deposit): ?ReferralCommission
    {
        if (! $this->settings->get('referral_enabled', true)) {
            return null;
        }

        // Only live wallet deposits earn commission
        if ($deposit->wallet_type !== 'live') {
            return null;
        }

        $referred = $deposit->user;
        if (! $referred || ! $referred->referred_by) {
            return null;
        }

        $minDeposit = (float) $this->settings->get('referral_min_deposit_usd', 1);
        if ((float) $deposit->usd_amount < $minDeposit) {
            return null;
        }

        $referrer = User::find($referred->referred_by);
        if (! $referrer || $referrer->is_banned) {
            return null;
        }

        $rate             = (float) $this->settings->get('referral_commission_rate', 3);
        $depositAmountUsd = (float) $deposit->usd_amount;
        $commissionUsd    = round($depositAmountUsd * $rate / 100, 8);

        if ($commissionUsd <= 0) {
            return null;
        }

        try {
            return DB::transaction(function () use (
                $referrer, $referred, $deposit, $rate, $depositAmountUsd, $commissionUsd
            ) {
                // Idempotency: unique constraint on deposit_id will throw on duplicate
                $commission = ReferralCommission::create([
                    'referrer_id'          => $referrer->id,
                    'referred_id'          => $referred->id,
                    'deposit_id'           => $deposit->id,
                    'deposit_amount_usd'   => $depositAmountUsd,
                    'rate'                 => $rate,
                    'commission_amount_usd'=> $commissionUsd,
                    'status'               => 'paid',
                ]);

                $liveWallet = $this->walletService->getUserWallet($referrer, 'live');

                $txn = $this->walletService->credit(
                    $liveWallet,
                    $commissionUsd,
                    'referral_commission',
                    "Referral commission: {$referred->name} deposited \${$depositAmountUsd}",
                    [
                        'referral_commission_id' => $commission->id,
                        'referred_user_id'       => $referred->id,
                        'deposit_id'             => $deposit->id,
                        'rate_percent'           => $rate,
                    ]
                );

                $commission->update(['transaction_id' => $txn->id]);

                Log::info('Referral commission paid', [
                    'referrer_id'   => $referrer->id,
                    'referred_id'   => $referred->id,
                    'deposit_id'    => $deposit->id,
                    'commission'    => $commissionUsd,
                ]);

                return $commission->fresh();
            });
        } catch (\Illuminate\Database\UniqueConstraintViolationException) {
            // Already processed for this deposit — safe to ignore
            Log::info('Referral commission already exists for deposit', ['deposit_id' => $deposit->id]);
            return null;
        } catch (\Throwable $e) {
            Log::error('Referral commission failed', [
                'deposit_id' => $deposit->id,
                'error'      => $e->getMessage(),
            ]);

            // Record the failure without breaking the deposit flow
            ReferralCommission::updateOrCreate(
                ['deposit_id' => $deposit->id],
                [
                    'referrer_id'          => $referrer->id,
                    'referred_id'          => $referred->id,
                    'deposit_amount_usd'   => $depositAmountUsd,
                    'rate'                 => $rate,
                    'commission_amount_usd'=> $commissionUsd,
                    'status'               => 'failed',
                    'notes'                => $e->getMessage(),
                ]
            );

            return null;
        }
    }

    /**
     * Total commission earned by a user (paid only).
     */
    public function totalEarned(User $user): float
    {
        return (float) ReferralCommission::where('referrer_id', $user->id)
            ->where('status', 'paid')
            ->sum('commission_amount_usd');
    }

    /**
     * Count of distinct referred users who made at least one deposit.
     */
    public function activeReferralCount(User $user): int
    {
        return ReferralCommission::where('referrer_id', $user->id)
            ->where('status', 'paid')
            ->distinct('referred_id')
            ->count('referred_id');
    }
}
