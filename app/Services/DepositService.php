<?php

namespace App\Services;

use App\Models\PaymentDeposit;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use App\Services\ReferralService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class DepositService
{
    public function __construct(
        private PalPlussService $palpluss,
        private CurrencyService $currency,
        private WalletService   $walletService,
        private SettingsService $settings,
        private ReferralService $referralService
    ) {}

    public function initiateMpesaDeposit(
        User   $user,
        float  $kesAmount,
        string $phone,
        string $walletType
    ): PaymentDeposit {
        if (!$this->settings->boolean('deposits_enabled', true)) {
            throw new RuntimeException('Deposits are currently disabled. Please try again later.');
        }
        if (!$this->settings->boolean('mpesa_deposits_enabled', true)) {
            throw new RuntimeException('M-Pesa deposits are currently disabled. Please try again later.');
        }
        if (!$user->isKenya()) {
            throw new RuntimeException('M-Pesa deposits are only available for Kenya accounts.');
        }

        $wallet      = $this->walletService->getUserWallet($user, $walletType);
        $usdAmount   = $this->currency->kesToUsd($kesAmount);
        $rate        = $this->currency->getUsdKesRate();
        $accountRef  = $this->generateAccountReference($user->id);
        $normalPhone = $this->palpluss->normalizePhone($phone);

        $deposit = DB::transaction(function () use (
            $user, $wallet, $walletType, $kesAmount, $usdAmount,
            $rate, $phone, $normalPhone, $accountRef
        ) {
            return PaymentDeposit::create([
                'user_id'                    => $user->id,
                'wallet_id'                  => $wallet->id,
                'wallet_type'                => $walletType,
                'provider'                   => 'palpluss',
                'method'                     => 'mpesa_stk',
                'local_amount'               => $kesAmount,
                'local_currency'             => 'KES',
                'usd_amount'                 => $usdAmount,
                'exchange_rate'              => $rate,
                'phone'                      => $normalPhone,
                'account_reference'          => $accountRef,
                'status'                     => 'pending',
                'manual_refresh_available_at'=> now()->addSeconds(10),
                'metadata'                   => [
                    'raw_phone'    => $phone,
                    'wallet_type'  => $walletType,
                    'initiated_at' => now()->toISOString(),
                ],
            ]);
        });

        // Call PalPluss STK
        $result = $this->palpluss->initiateStk([
            'amount'          => (int) $kesAmount,
            'phone'           => $normalPhone,
            'accountReference'=> $accountRef,
            'transactionDesc' => "NextTrade deposit ({$accountRef})",
        ]);

        // Store initiation response regardless of outcome
        $updates = ['raw_initiation_response' => $result['raw'] ?? []];

        if ($result['success']) {
            $data = $result['data'];
            $updates['provider_transaction_id'] = $data['transactionId'] ?? null;
            $updates['provider_request_id']     = $data['providerRequestId'] ?? null;
            $updates['provider_checkout_id']    = $data['providerCheckoutId'] ?? null;
            $updates['provider_status']         = $data['status'] ?? 'PENDING';
            $updates['result_code']             = $data['resultCode'] ?? null;
            $updates['result_description']      = $data['resultDescription'] ?? null;
        } else {
            // STK initiation failed — mark deposit failed immediately
            $updates['status']             = 'failed';
            $updates['result_description'] = $result['message'] ?? 'STK initiation failed';

            $deposit->update($updates);

            throw new RuntimeException($result['message'] ?? 'Failed to send STK push. Please try again.');
        }

        $deposit->update($updates);

        return $deposit->fresh();
    }

    public function handlePalplussCallback(array $payload): ?PaymentDeposit
    {
        $transaction = $payload['transaction'] ?? [];
        $txId        = $transaction['id'] ?? null;
        $extRef      = $transaction['external_reference'] ?? null;

        Log::info('PalPluss callback received', [
            'event'          => $payload['event'] ?? null,
            'transaction_id' => $txId,
            'status'         => $transaction['status'] ?? null,
        ]);

        // Find deposit by provider_transaction_id first, fall back to account_reference
        $deposit = null;

        if ($txId) {
            $deposit = PaymentDeposit::where('provider_transaction_id', $txId)->first();
        }

        if (!$deposit && $extRef) {
            $deposit = PaymentDeposit::where('account_reference', $extRef)->first();
        }

        if (!$deposit) {
            Log::warning('PalPluss callback: deposit not found', compact('txId', 'extRef'));
            return null;
        }

        // Ensure provider_transaction_id is stored if we found by account_reference
        if ($txId && !$deposit->provider_transaction_id) {
            $deposit->update(['provider_transaction_id' => $txId]);
        }

        $status = strtoupper($transaction['status'] ?? '');

        if ($status === 'SUCCESS') {
            return $this->creditDepositIfSuccessful($deposit, $transaction, 'callback');
        }

        if (in_array($status, ['FAILED', 'CANCELLED'])) {
            return $this->markDepositFailed($deposit, $transaction);
        }

        // Still pending — update raw callback response
        $deposit->update([
            'provider_status'       => $status,
            'raw_callback_response' => $transaction,
        ]);

        return $deposit->fresh();
    }

    public function refreshMpesaDepositStatus(User $user, PaymentDeposit $deposit): PaymentDeposit
    {
        if ($deposit->user_id !== $user->id) {
            throw new RuntimeException('Unauthorized.');
        }

        if (!$deposit->isPending()) {
            throw new RuntimeException('This deposit is already ' . $deposit->status . '.');
        }

        if (!$deposit->provider_transaction_id) {
            throw new RuntimeException('No provider reference available for this deposit.');
        }

        if ($deposit->manual_refresh_available_at && now()->lt($deposit->manual_refresh_available_at)) {
            $seconds = (int) now()->diffInSeconds($deposit->manual_refresh_available_at, false) * -1;
            $wait    = max(1, $seconds);
            throw new RuntimeException("Please wait {$wait} second(s) before checking payment status.");
        }

        $result = $this->palpluss->getTransactionStatus($deposit->provider_transaction_id);

        $deposit->increment('status_check_count');
        $deposit->update(['last_status_checked_at' => now()]);

        if (!$result['success']) {
            throw new RuntimeException($result['message'] ?? 'Could not fetch payment status.');
        }

        $data   = $result['data'];
        $status = strtoupper($data['status'] ?? '');

        // Always store the raw status response
        $deposit->update(['raw_status_response' => $data]);

        if ($status === 'SUCCESS') {
            return $this->creditDepositIfSuccessful($deposit->fresh(), $data, 'manual_status_refresh');
        }

        if (in_array($status, ['FAILED', 'CANCELLED'])) {
            return $this->markDepositFailed($deposit->fresh(), $data);
        }

        // Still PENDING
        $deposit->update(['provider_status' => $status]);

        return $deposit->fresh();
    }

    public function creditDepositIfSuccessful(
        PaymentDeposit $deposit,
        array          $providerData,
        string         $source
    ): PaymentDeposit {
        return DB::transaction(function () use ($deposit, $providerData, $source) {
            // Re-fetch with row lock for idempotency
            $deposit = PaymentDeposit::lockForUpdate()->find($deposit->id);

            // Already credited — idempotency: only update raw fields, no double credit
            if ($deposit->isCredited()) {
                $rawField = $source === 'callback' ? 'raw_callback_response' : 'raw_status_response';
                $deposit->update([$rawField => $providerData]);
                Log::info("PalPluss deposit already credited — skipping duplicate ({$source})", [
                    'deposit_id' => $deposit->id,
                ]);
                return $deposit->fresh();
            }

            $mpesaReceipt = $providerData['mpesa_receipt']
                ?? $providerData['mpesaReceipt']
                ?? $deposit->mpesa_receipt;

            $this->walletService->credit(
                $deposit->wallet,
                (float) $deposit->usd_amount,
                'deposit',
                "M-Pesa Deposit: KES {$deposit->local_amount} via {$deposit->account_reference}",
                [
                    'deposit_id'             => $deposit->id,
                    'account_reference'      => $deposit->account_reference,
                    'provider_transaction_id'=> $deposit->provider_transaction_id,
                    'mpesa_receipt'          => $mpesaReceipt,
                    'credited_source'        => $source,
                ]
            );

            $rawUpdates = $source === 'callback'
                ? ['raw_callback_response' => $providerData]
                : ['raw_status_response'   => $providerData];

            $deposit->update(array_merge($rawUpdates, [
                'status'             => 'successful',
                'provider_status'    => 'SUCCESS',
                'credited_at'        => now(),
                'mpesa_receipt'      => $mpesaReceipt,
                'result_code'        => $providerData['result_code'] ?? $providerData['resultCode'] ?? $deposit->result_code,
                'result_description' => $providerData['result_desc'] ?? $providerData['resultDesc'] ?? $deposit->result_description,
                'metadata'           => array_merge($deposit->metadata ?? [], ['credited_source' => $source]),
            ]));

            Log::info("Deposit credited successfully via {$source}", [
                'deposit_id'  => $deposit->id,
                'usd_amount'  => $deposit->usd_amount,
                'user_id'     => $deposit->user_id,
            ]);

            $fresh = $deposit->fresh();

            // Fire referral commission outside the lock — failure must not roll back the credit
            $this->referralService->processDepositCommission($fresh);

            return $fresh;
        });
    }

    public function markDepositFailed(PaymentDeposit $deposit, array $providerData): PaymentDeposit
    {
        if (!$deposit->isPending()) {
            return $deposit;
        }

        $deposit->update([
            'status'             => 'failed',
            'provider_status'    => $providerData['status'] ?? 'FAILED',
            'result_code'        => $providerData['result_code'] ?? $providerData['resultCode'] ?? null,
            'result_description' => $providerData['result_desc'] ?? $providerData['resultDesc'] ?? 'Payment failed',
        ]);

        return $deposit->fresh();
    }

    public function createUsdtManualDeposit(
        User         $user,
        float        $amount,
        string       $txid,
        UploadedFile $proof,
        string       $walletType
    ): PaymentDeposit {
        if (!$this->settings->boolean('deposits_enabled', true)) {
            throw new RuntimeException('Deposits are currently disabled. Please try again later.');
        }
        if (!$this->settings->boolean('usdt_deposits_enabled', true)) {
            throw new RuntimeException('USDT deposits are currently disabled. Please try again later.');
        }
        $wallet = $this->walletService->getUserWallet($user, $walletType);

        if ($wallet->isFrozen()) {
            throw new RuntimeException('Your wallet is frozen. Please contact support.');
        }

        $rate      = (float) config('crypto.usdt_usd_rate', 1);
        $usdAmount = round($amount * $rate, 2);
        $address   = config('crypto.usdt_trc20_wallet_address', '');
        $network   = config('crypto.usdt_trc20_network', 'TRC20');
        $accountRef= 'NT-USDT-' . $user->id . '-' . now()->format('YmdHis') . '-' . strtoupper(Str::random(6));

        $proofPath = $proof->store('deposits/usdt', 'public');

        if (!$proofPath) {
            throw new RuntimeException('Failed to upload proof screenshot. Please try again.');
        }

        return DB::transaction(function () use (
            $user, $wallet, $walletType, $amount, $usdAmount,
            $rate, $txid, $proofPath, $address, $network, $accountRef
        ) {
            return PaymentDeposit::create([
                'user_id'        => $user->id,
                'wallet_id'      => $wallet->id,
                'wallet_type'    => $walletType,
                'provider'       => 'usdt_manual',
                'method'         => 'crypto_usdt_trc20',
                'local_amount'   => $amount,
                'local_currency' => 'USDT',
                'usd_amount'     => $usdAmount,
                'exchange_rate'  => $rate,
                'crypto_network' => $network,
                'crypto_address' => $address,
                'txid'           => $txid,
                'proof_path'     => $proofPath,
                'account_reference' => $accountRef,
                'status'         => 'pending',
                'provider_status'=> 'pending',
                'metadata'       => [
                    'wallet_type'  => $walletType,
                    'submitted_at' => now()->toISOString(),
                ],
            ]);
        });
    }

    public function approveMpesaDeposit(
        PaymentDeposit $deposit,
        User           $admin,
        ?string        $notes = null
    ): PaymentDeposit {
        if ($deposit->method !== 'mpesa') {
            throw new RuntimeException('This deposit is not an M-Pesa deposit.');
        }

        if (!$deposit->isPending()) {
            throw new RuntimeException('Deposit is already ' . $deposit->status . '. Cannot approve.');
        }

        return DB::transaction(function () use ($deposit, $admin, $notes) {
            $deposit = PaymentDeposit::lockForUpdate()->find($deposit->id);

            if ($deposit->isCredited()) {
                throw new RuntimeException('This deposit has already been credited. Double-approval prevented.');
            }

            if (!$deposit->isPending()) {
                throw new RuntimeException('Deposit status changed to ' . $deposit->status . '. Cannot approve.');
            }

            $this->walletService->credit(
                $deposit->wallet,
                (float) $deposit->usd_amount,
                'deposit',
                "M-Pesa Deposit: KES {$deposit->local_amount} — manual admin approval",
                [
                    'deposit_id'        => $deposit->id,
                    'account_reference' => $deposit->account_reference,
                    'credited_source'   => 'admin_mpesa_approval',
                ]
            );

            $deposit->update([
                'status'          => 'successful',
                'provider_status' => 'SUCCESS',
                'credited_at'     => now(),
                'reviewed_by'     => $admin->id,
                'reviewed_at'     => now(),
                'admin_notes'     => $notes,
                'metadata'        => array_merge($deposit->metadata ?? [], [
                    'credited_source' => 'admin_mpesa_approval',
                    'approved_by'     => $admin->id,
                ]),
            ]);

            Log::info('M-Pesa deposit manually approved', [
                'deposit_id' => $deposit->id,
                'admin_id'   => $admin->id,
                'usd_amount' => $deposit->usd_amount,
            ]);

            $fresh = $deposit->fresh();

            $this->referralService->processDepositCommission($fresh);

            return $fresh;
        });
    }

    public function approveUsdtDeposit(
        PaymentDeposit $deposit,
        User           $admin,
        ?string        $notes = null
    ): PaymentDeposit {
        if ($deposit->method !== 'crypto_usdt_trc20') {
            throw new RuntimeException('This deposit is not a USDT deposit.');
        }

        if (!$deposit->isPending()) {
            throw new RuntimeException('Deposit is already ' . $deposit->status . '. Cannot approve.');
        }

        return DB::transaction(function () use ($deposit, $admin, $notes) {
            $deposit = PaymentDeposit::lockForUpdate()->find($deposit->id);

            if ($deposit->isCredited()) {
                throw new RuntimeException('This deposit has already been credited. Double-approval prevented.');
            }

            if (!$deposit->isPending()) {
                throw new RuntimeException('Deposit status changed to ' . $deposit->status . '. Cannot approve.');
            }

            $this->walletService->credit(
                $deposit->wallet,
                (float) $deposit->usd_amount,
                'deposit',
                "USDT Deposit: {$deposit->local_amount} USDT via {$deposit->account_reference}",
                [
                    'deposit_id'        => $deposit->id,
                    'account_reference' => $deposit->account_reference,
                    'txid'              => $deposit->txid,
                    'credited_source'   => 'admin_usdt_approval',
                ]
            );

            $deposit->update([
                'status'          => 'successful',
                'provider_status' => 'approved',
                'credited_at'     => now(),
                'reviewed_by'     => $admin->id,
                'reviewed_at'     => now(),
                'admin_notes'     => $notes,
                'metadata'        => array_merge($deposit->metadata ?? [], [
                    'credited_source' => 'admin_usdt_approval',
                    'approved_by'     => $admin->id,
                ]),
            ]);

            Log::info('USDT deposit approved', [
                'deposit_id' => $deposit->id,
                'admin_id'   => $admin->id,
                'usd_amount' => $deposit->usd_amount,
                'txid'       => $deposit->txid,
            ]);

            $fresh = $deposit->fresh();

            $this->referralService->processDepositCommission($fresh);

            return $fresh;
        });
    }

    public function rejectUsdtDeposit(
        PaymentDeposit $deposit,
        User           $admin,
        string         $reason,
        ?string        $notes = null
    ): PaymentDeposit {
        if ($deposit->method !== 'crypto_usdt_trc20') {
            throw new RuntimeException('This deposit is not a USDT deposit.');
        }

        if (!$deposit->isPending()) {
            throw new RuntimeException('Deposit is already ' . $deposit->status . '. Cannot reject.');
        }

        if ($deposit->isCredited()) {
            throw new RuntimeException('This deposit has already been credited. Cannot reject.');
        }

        $deposit->update([
            'status'           => 'failed',
            'provider_status'  => 'rejected',
            'rejection_reason' => $reason,
            'admin_notes'      => $notes,
            'reviewed_by'      => $admin->id,
            'reviewed_at'      => now(),
        ]);

        Log::info('USDT deposit rejected', [
            'deposit_id' => $deposit->id,
            'admin_id'   => $admin->id,
            'reason'     => $reason,
        ]);

        return $deposit->fresh();
    }

    private function generateAccountReference(int $userId): string
    {
        return 'NT-DEP-' . $userId . '-' . now()->format('YmdHis') . '-' . strtoupper(Str::random(6));
    }
}
