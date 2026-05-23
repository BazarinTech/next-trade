<?php

namespace App\Services;

use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class WalletService
{
    public const DEMO_STARTING_BALANCE = 10000.00;

    /**
     * Get a specific wallet for a user (creates it if missing).
     */
    public function getUserWallet(User $user, string $type): Wallet
    {
        return $user->wallets()->firstOrCreate(
            ['type' => $type],
            [
                'currency'  => 'USD',
                'balance'   => $type === 'demo' ? self::DEMO_STARTING_BALANCE : 0,
                'status'    => 'active',
            ]
        );
    }

    /**
     * Create both demo and live wallets for a new user.
     */
    public function createDefaultWallets(User $user): void
    {
        DB::transaction(function () use ($user) {
            $demo = Wallet::firstOrCreate(
                ['user_id' => $user->id, 'type' => 'demo'],
                ['currency' => 'USD', 'balance' => self::DEMO_STARTING_BALANCE, 'status' => 'active']
            );

            if ($demo->wasRecentlyCreated) {
                $this->recordTransaction($demo, [
                    'type'           => 'adjustment',
                    'amount'         => self::DEMO_STARTING_BALANCE,
                    'balance_before' => '0',
                    'balance_after'  => (string) self::DEMO_STARTING_BALANCE,
                    'description'    => 'Demo account funded',
                    'status'         => 'successful',
                    'metadata'       => ['source' => 'registration'],
                ]);
            }

            Wallet::firstOrCreate(
                ['user_id' => $user->id, 'type' => 'live'],
                ['currency' => 'USD', 'balance' => 0, 'status' => 'active']
            );
        });
    }

    /**
     * Credit a wallet — increases balance.
     */
    public function credit(
        Wallet $wallet,
        float|string $amount,
        string $type,
        string $description = '',
        array $metadata = []
    ): Transaction {
        $this->guardFrozen($wallet);
        $this->guardPositiveAmount($amount);

        return DB::transaction(function () use ($wallet, $amount, $type, $description, $metadata) {
            $wallet->lockForUpdate()->find($wallet->id); // re-fetch with lock
            $wallet->refresh();

            $before = (string) $wallet->balance;
            $after  = bcadd($before, (string) $amount, 8);

            $wallet->balance = $after;

            // Update running totals
            if ($type === 'deposit') {
                $wallet->total_deposited = bcadd((string) $wallet->total_deposited, (string) $amount, 8);
            } elseif (in_array($type, ['trade_profit', 'bot_profit'])) {
                $wallet->total_profit = bcadd((string) $wallet->total_profit, (string) $amount, 8);
            }

            $wallet->save();

            return $this->recordTransaction($wallet, [
                'type'           => $type,
                'amount'         => $amount,
                'balance_before' => $before,
                'balance_after'  => $after,
                'description'    => $description,
                'status'         => 'successful',
                'metadata'       => $metadata,
            ]);
        });
    }

    /**
     * Debit a wallet — decreases balance.
     */
    public function debit(
        Wallet $wallet,
        float|string $amount,
        string $type,
        string $description = '',
        array $metadata = []
    ): Transaction {
        $this->guardFrozen($wallet);
        $this->guardPositiveAmount($amount);

        return DB::transaction(function () use ($wallet, $amount, $type, $description, $metadata) {
            $wallet->lockForUpdate()->find($wallet->id);
            $wallet->refresh();

            $available = $wallet->available_balance;

            if (bccomp((string) $amount, $available, 8) > 0) {
                throw new RuntimeException(
                    "Insufficient balance. Available: {$available} USD, Requested: {$amount} USD."
                );
            }

            $before = (string) $wallet->balance;
            $after  = bcsub($before, (string) $amount, 8);

            $wallet->balance = $after;

            // Update running totals
            if ($type === 'withdrawal') {
                $wallet->total_withdrawn = bcadd((string) $wallet->total_withdrawn, (string) $amount, 8);
            } elseif (in_array($type, ['trade_loss', 'bot_investment'])) {
                $wallet->total_loss = bcadd((string) $wallet->total_loss, (string) $amount, 8);
            }

            $wallet->save();

            return $this->recordTransaction($wallet, [
                'type'           => $type,
                'amount'         => $amount,
                'balance_before' => $before,
                'balance_after'  => $after,
                'description'    => $description,
                'status'         => 'successful',
                'metadata'       => $metadata,
            ]);
        });
    }

    /**
     * Lock (reserve) an amount — moves balance to locked_balance.
     */
    public function lockAmount(Wallet $wallet, float|string $amount): void
    {
        $this->guardFrozen($wallet);
        $this->guardPositiveAmount($amount);

        DB::transaction(function () use ($wallet, $amount) {
            $wallet->lockForUpdate()->find($wallet->id);
            $wallet->refresh();

            if (bccomp((string) $amount, $wallet->available_balance, 8) > 0) {
                throw new RuntimeException('Insufficient available balance to lock.');
            }

            $wallet->locked_balance = bcadd((string) $wallet->locked_balance, (string) $amount, 8);
            $wallet->save();
        });
    }

    /**
     * Unlock a previously locked amount.
     */
    public function unlockAmount(Wallet $wallet, float|string $amount): void
    {
        $this->guardFrozen($wallet);

        DB::transaction(function () use ($wallet, $amount) {
            $wallet->lockForUpdate()->find($wallet->id);
            $wallet->refresh();

            $newLocked = bcsub((string) $wallet->locked_balance, (string) $amount, 8);

            if (bccomp($newLocked, '0', 8) < 0) {
                $newLocked = '0';
            }

            $wallet->locked_balance = $newLocked;
            $wallet->save();
        });
    }

    /**
     * Permanently deduct from locked_balance (for approved withdrawals).
     * Reduces both balance and locked_balance — funds leave the platform.
     */
    public function deductLockedAmount(
        Wallet $wallet,
        float|string $amount,
        string $type,
        string $description = '',
        array $metadata = []
    ): Transaction {
        $this->guardPositiveAmount($amount);

        return DB::transaction(function () use ($wallet, $amount, $type, $description, $metadata) {
            $wallet->lockForUpdate()->find($wallet->id);
            $wallet->refresh();

            if (bccomp((string) $amount, (string) $wallet->locked_balance, 8) > 0) {
                throw new RuntimeException(
                    "Locked balance insufficient. Locked: {$wallet->locked_balance}, Requested: {$amount}."
                );
            }

            $before = (string) $wallet->balance;
            $after  = bcsub($before, (string) $amount, 8);

            if (bccomp($after, '0', 8) < 0) {
                throw new RuntimeException('Balance would go negative. Aborting withdrawal deduction.');
            }

            $wallet->balance         = $after;
            $wallet->locked_balance  = bcsub((string) $wallet->locked_balance, (string) $amount, 8);
            $wallet->total_withdrawn = bcadd((string) $wallet->total_withdrawn, (string) $amount, 8);
            $wallet->save();

            return $this->recordTransaction($wallet, [
                'type'           => $type,
                'amount'         => $amount,
                'balance_before' => $before,
                'balance_after'  => $after,
                'description'    => $description,
                'status'         => 'successful',
                'metadata'       => $metadata,
            ]);
        });
    }

    /**
     * Move locked balance back to available balance.
     */
    public function transferLockedToAvailable(Wallet $wallet, float|string $amount): void
    {
        DB::transaction(function () use ($wallet, $amount) {
            $wallet->lockForUpdate()->find($wallet->id);
            $wallet->refresh();

            $unlock = min((float) $amount, (float) $wallet->locked_balance);

            $wallet->locked_balance = bcsub((string) $wallet->locked_balance, (string) $unlock, 8);
            $wallet->save();
        });
    }

    /**
     * Reset demo wallet back to 10,000 USD.
     */
    public function resetDemoWallet(User $user): Wallet
    {
        $wallet = $user->wallets()->where('type', 'demo')->firstOrFail();

        DB::transaction(function () use ($wallet, $user) {
            $before = (string) $wallet->balance;

            $wallet->balance         = self::DEMO_STARTING_BALANCE;
            $wallet->locked_balance  = 0;
            $wallet->total_deposited = 0;
            $wallet->total_withdrawn = 0;
            $wallet->total_profit    = 0;
            $wallet->total_loss      = 0;
            $wallet->status          = 'active';
            $wallet->save();

            $this->recordTransaction($wallet, [
                'type'           => 'adjustment',
                'amount'         => self::DEMO_STARTING_BALANCE,
                'balance_before' => $before,
                'balance_after'  => (string) self::DEMO_STARTING_BALANCE,
                'description'    => 'Demo wallet reset to $10,000',
                'status'         => 'successful',
                'metadata'       => ['source' => 'demo_reset', 'reset_at' => now()->toISOString()],
            ]);
        });

        return $wallet->fresh();
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    private function recordTransaction(Wallet $wallet, array $data): Transaction
    {
        return Transaction::create([
            'user_id'        => $wallet->user_id,
            'wallet_id'      => $wallet->id,
            'type'           => $data['type'],
            'amount'         => $data['amount'],
            'currency'       => $wallet->currency,
            'balance_before' => $data['balance_before'],
            'balance_after'  => $data['balance_after'],
            'reference'      => $data['reference'] ?? $this->generateReference(),
            'description'    => $data['description'] ?? null,
            'status'         => $data['status'] ?? 'successful',
            'metadata'       => $data['metadata'] ?? null,
        ]);
    }

    private function generateReference(): string
    {
        return strtoupper('TXN-' . now()->format('Ymd') . '-' . Str::random(8));
    }

    private function guardFrozen(Wallet $wallet): void
    {
        if ($wallet->isFrozen()) {
            throw new RuntimeException('This wallet is frozen. Please contact support.');
        }
    }

    private function guardPositiveAmount(float|string $amount): void
    {
        if (bccomp((string) $amount, '0', 8) <= 0) {
            throw new RuntimeException('Amount must be greater than zero.');
        }
    }
}
