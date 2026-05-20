<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentDeposit extends Model
{
    protected $fillable = [
        'user_id', 'wallet_id', 'wallet_type', 'provider', 'method',
        'local_amount', 'local_currency', 'usd_amount', 'exchange_rate',
        'crypto_network', 'crypto_address', 'txid', 'proof_path',
        'phone', 'account_reference', 'provider_transaction_id',
        'provider_request_id', 'provider_checkout_id', 'mpesa_receipt',
        'provider_status', 'status', 'result_code', 'result_description',
        'raw_initiation_response', 'raw_callback_response', 'raw_status_response',
        'credited_at', 'manual_refresh_available_at', 'last_status_checked_at',
        'status_check_count', 'metadata',
        'reviewed_by', 'reviewed_at', 'rejection_reason', 'admin_notes',
    ];

    protected function casts(): array
    {
        return [
            'local_amount'               => 'decimal:2',
            'usd_amount'                 => 'decimal:8',
            'exchange_rate'              => 'decimal:6',
            'raw_initiation_response'    => 'array',
            'raw_callback_response'      => 'array',
            'raw_status_response'        => 'array',
            'metadata'                   => 'array',
            'credited_at'                => 'datetime',
            'manual_refresh_available_at'=> 'datetime',
            'last_status_checked_at'     => 'datetime',
            'reviewed_at'                => 'datetime',
            'status_check_count'         => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isSuccessful(): bool
    {
        return $this->status === 'successful';
    }

    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    public function isCredited(): bool
    {
        return $this->credited_at !== null;
    }

    public function isUsdtDeposit(): bool
    {
        return $this->method === 'crypto_usdt_trc20';
    }

    public function canManualRefresh(): bool
    {
        return $this->isPending()
            && $this->provider_transaction_id !== null
            && $this->manual_refresh_available_at !== null
            && now()->gte($this->manual_refresh_available_at);
    }

    public function secondsUntilRefresh(): int
    {
        if (!$this->manual_refresh_available_at) {
            return 0;
        }
        return max(0, (int) now()->diffInSeconds($this->manual_refresh_available_at, false));
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'pending'    => 'amber',
            'successful' => 'emerald',
            'failed'     => 'red',
            'cancelled'  => 'gray',
            default      => 'gray',
        };
    }

    public function getMethodLabelAttribute(): string
    {
        return match ($this->method) {
            'mpesa_stk'         => 'M-Pesa STK',
            'crypto_usdt_trc20' => 'USDT (TRC20)',
            default             => ucfirst($this->method),
        };
    }

    public function getProofUrlAttribute(): ?string
    {
        if (!$this->proof_path) {
            return null;
        }
        return asset('storage/' . $this->proof_path);
    }
}
