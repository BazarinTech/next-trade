<?php

namespace App\Services;

use App\Models\User;
use App\Models\Wallet;
use App\Models\Withdrawal;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use RuntimeException;

class WithdrawalService
{
    public function __construct(
        private WalletService   $walletService,
        private CurrencyService $currency,
        private SettingsService $settings
    ) {}

    public function createMpesaWithdrawal(
        User   $user,
        float  $usdAmount,
        string $phone
    ): Withdrawal {
        if (!$this->settings->boolean('withdrawals_enabled', true)) {
            throw new RuntimeException('Withdrawals are currently disabled. Please try again later.');
        }
        if (!$user->isKenya()) {
            throw new RuntimeException('M-Pesa withdrawals are only available for Kenya accounts.');
        }

        $wallet = $this->getLiveWallet($user);
        $this->guardWallet($wallet, $usdAmount);

        $fee        = $this->calculateWithdrawalFee($usdAmount, 'mpesa');
        $netAmount  = round($usdAmount - $fee, 8);
        $kesAmount  = $this->currency->usdToKes($usdAmount);
        $rate       = $this->currency->getUsdKesRate();
        $ref        = $this->generateReference($user);

        return DB::transaction(function () use (
            $user, $wallet, $usdAmount, $netAmount, $fee,
            $kesAmount, $rate, $phone, $ref
        ) {
            $this->walletService->lockAmount($wallet, $usdAmount);

            return Withdrawal::create([
                'user_id'           => $user->id,
                'wallet_id'         => $wallet->id,
                'method'            => 'mpesa',
                'status'            => 'pending',
                'usd_amount'        => $usdAmount,
                'local_amount'      => $kesAmount,
                'local_currency'    => 'KES',
                'exchange_rate'     => $rate,
                'phone'             => $phone,
                'account_reference' => $ref,
                'fee_amount'        => $fee,
                'net_amount'        => $netAmount,
                'requested_at'      => now(),
                'metadata'          => ['requested_at' => now()->toISOString()],
            ]);
        });
    }

    public function createUsdtWithdrawal(
        User   $user,
        float  $usdAmount,
        string $cryptoAddress
    ): Withdrawal {
        if (!$this->settings->boolean('withdrawals_enabled', true)) {
            throw new RuntimeException('Withdrawals are currently disabled. Please try again later.');
        }
        $wallet    = $this->getLiveWallet($user);
        $this->guardWallet($wallet, $usdAmount);

        $fee       = $this->calculateWithdrawalFee($usdAmount, 'usdt_trc20');
        $netAmount = round($usdAmount - $fee, 8);
        $ref       = $this->generateReference($user);

        return DB::transaction(function () use (
            $user, $wallet, $usdAmount, $netAmount, $fee, $cryptoAddress, $ref
        ) {
            $this->walletService->lockAmount($wallet, $usdAmount);

            return Withdrawal::create([
                'user_id'           => $user->id,
                'wallet_id'         => $wallet->id,
                'method'            => 'usdt_trc20',
                'status'            => 'pending',
                'usd_amount'        => $usdAmount,
                'local_amount'      => $netAmount,
                'local_currency'    => 'USDT',
                'exchange_rate'     => 1.0,
                'crypto_network'    => 'TRC20',
                'crypto_address'    => $cryptoAddress,
                'account_reference' => $ref,
                'fee_amount'        => $fee,
                'net_amount'        => $netAmount,
                'requested_at'      => now(),
                'metadata'          => ['requested_at' => now()->toISOString()],
            ]);
        });
    }

    public function approveWithdrawal(
        Withdrawal $withdrawal,
        User       $admin,
        ?string    $notes = null
    ): Withdrawal {
        $this->guardNotTerminal($withdrawal, 'approve');

        $withdrawal->update([
            'status'      => 'approved',
            'reviewed_by' => $admin->id,
            'reviewed_at' => now(),
            'admin_notes' => $notes,
        ]);

        Log::info('Withdrawal approved', [
            'withdrawal_id' => $withdrawal->id,
            'admin_id'      => $admin->id,
        ]);

        return $withdrawal->fresh();
    }

    public function rejectWithdrawal(
        Withdrawal $withdrawal,
        User       $admin,
        string     $reason,
        ?string    $notes = null
    ): Withdrawal {
        $this->guardNotTerminal($withdrawal, 'reject');

        return DB::transaction(function () use ($withdrawal, $admin, $reason, $notes) {
            $withdrawal = Withdrawal::lockForUpdate()->find($withdrawal->id);

            if ($withdrawal->isTerminal()) {
                throw new RuntimeException('Withdrawal is already ' . $withdrawal->status . '.');
            }

            // Unlock the reserved funds
            $this->walletService->unlockAmount($withdrawal->wallet, (float) $withdrawal->usd_amount);

            $withdrawal->update([
                'status'           => 'rejected',
                'rejection_reason' => $reason,
                'admin_notes'      => $notes,
                'reviewed_by'      => $admin->id,
                'reviewed_at'      => now(),
            ]);

            Log::info('Withdrawal rejected, funds unlocked', [
                'withdrawal_id' => $withdrawal->id,
                'admin_id'      => $admin->id,
                'usd_amount'    => $withdrawal->usd_amount,
            ]);

            return $withdrawal->fresh();
        });
    }

    public function markWithdrawalProcessing(
        Withdrawal $withdrawal,
        User       $admin,
        ?string    $providerReference = null
    ): Withdrawal {
        if (!in_array($withdrawal->status, ['pending', 'approved'])) {
            throw new RuntimeException('Withdrawal must be pending or approved to mark as processing.');
        }

        $withdrawal->update([
            'status'             => 'processing',
            'provider_reference' => $providerReference ?? $withdrawal->provider_reference,
            'processed_at'       => now(),
            'reviewed_by'        => $withdrawal->reviewed_by ?? $admin->id,
            'reviewed_at'        => $withdrawal->reviewed_at ?? now(),
            'admin_notes'        => $withdrawal->admin_notes,
        ]);

        return $withdrawal->fresh();
    }

    public function markWithdrawalSuccessful(
        Withdrawal $withdrawal,
        User       $admin,
        ?string    $providerReference = null,
        ?string    $txid = null
    ): Withdrawal {
        if ($withdrawal->isSuccessful()) {
            throw new RuntimeException('Withdrawal is already successful. Double-processing prevented.');
        }

        if ($withdrawal->isRejected() || $withdrawal->isFailed() || $withdrawal->isCancelled()) {
            throw new RuntimeException('Cannot mark a ' . $withdrawal->status . ' withdrawal as successful.');
        }

        return DB::transaction(function () use ($withdrawal, $admin, $providerReference, $txid) {
            $withdrawal = Withdrawal::lockForUpdate()->find($withdrawal->id);

            if ($withdrawal->isSuccessful()) {
                throw new RuntimeException('Withdrawal already marked successful. Double-processing prevented.');
            }

            // Permanently deduct locked funds
            $this->walletService->deductLockedAmount(
                $withdrawal->wallet,
                (float) $withdrawal->usd_amount,
                'withdrawal',
                "Withdrawal: {$withdrawal->usd_amount} USD via {$withdrawal->account_reference}",
                [
                    'withdrawal_id'     => $withdrawal->id,
                    'account_reference' => $withdrawal->account_reference,
                    'method'            => $withdrawal->method,
                    'provider_reference'=> $providerReference,
                    'txid'              => $txid,
                ]
            );

            $withdrawal->update([
                'status'             => 'successful',
                'provider_reference' => $providerReference ?? $withdrawal->provider_reference,
                'txid'               => $txid ?? $withdrawal->txid,
                'completed_at'       => now(),
                'reviewed_by'        => $withdrawal->reviewed_by ?? $admin->id,
                'reviewed_at'        => $withdrawal->reviewed_at ?? now(),
                'metadata'           => array_merge($withdrawal->metadata ?? [], [
                    'completed_by' => $admin->id,
                    'completed_at' => now()->toISOString(),
                ]),
            ]);

            Log::info('Withdrawal marked successful, funds deducted', [
                'withdrawal_id' => $withdrawal->id,
                'admin_id'      => $admin->id,
                'usd_amount'    => $withdrawal->usd_amount,
            ]);

            return $withdrawal->fresh();
        });
    }

    public function markWithdrawalFailed(
        Withdrawal $withdrawal,
        User       $admin,
        string     $reason
    ): Withdrawal {
        if ($withdrawal->isSuccessful()) {
            throw new RuntimeException('Cannot mark a successful withdrawal as failed.');
        }

        if ($withdrawal->isTerminal()) {
            throw new RuntimeException('Withdrawal is already ' . $withdrawal->status . '.');
        }

        return DB::transaction(function () use ($withdrawal, $admin, $reason) {
            $withdrawal = Withdrawal::lockForUpdate()->find($withdrawal->id);

            if ($withdrawal->isTerminal()) {
                throw new RuntimeException('Withdrawal is already ' . $withdrawal->status . '.');
            }

            // Unlock funds back to available
            $this->walletService->unlockAmount($withdrawal->wallet, (float) $withdrawal->usd_amount);

            $withdrawal->update([
                'status'           => 'failed',
                'rejection_reason' => $reason,
                'reviewed_by'      => $withdrawal->reviewed_by ?? $admin->id,
                'reviewed_at'      => $withdrawal->reviewed_at ?? now(),
            ]);

            Log::info('Withdrawal failed, funds unlocked', [
                'withdrawal_id' => $withdrawal->id,
                'admin_id'      => $admin->id,
            ]);

            return $withdrawal->fresh();
        });
    }

    public function calculateWithdrawalFee(float $amount, string $method): float
    {
        // Zero fee for now — configurable per method in future
        return 0.0;
    }

    public function generateReference(User $user): string
    {
        return 'NT-WD-' . $user->id . '-' . now()->format('YmdHis') . '-' . strtoupper(Str::random(6));
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    private function getLiveWallet(User $user): Wallet
    {
        $wallet = $user->wallets()->where('type', 'live')->first();

        if (!$wallet) {
            throw new RuntimeException('Live wallet not found.');
        }

        return $wallet;
    }

    private function guardWallet(Wallet $wallet, float $amount): void
    {
        if (!$wallet->isLive()) {
            throw new RuntimeException('Withdrawals are only allowed from the live wallet.');
        }

        if ($wallet->isFrozen()) {
            throw new RuntimeException('Your live wallet is frozen. Please contact support.');
        }

        if (bccomp((string) $amount, $wallet->available_balance, 8) > 0) {
            throw new RuntimeException(
                'Insufficient live wallet balance. Available: $' .
                number_format((float) $wallet->available_balance, 2) . ' USD.'
            );
        }

        if (bccomp((string) $amount, '0', 8) <= 0) {
            throw new RuntimeException('Withdrawal amount must be greater than zero.');
        }
    }

    private function guardNotTerminal(Withdrawal $withdrawal, string $action): void
    {
        if ($withdrawal->isTerminal()) {
            throw new RuntimeException(
                "Cannot {$action} a withdrawal that is already {$withdrawal->status}."
            );
        }
    }
}
