<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Withdrawal extends Model
{
    protected $fillable = [
        'user_id', 'wallet_id', 'method', 'status',
        'usd_amount', 'local_amount', 'local_currency', 'exchange_rate',
        'phone', 'crypto_network', 'crypto_address', 'txid',
        'account_reference', 'provider_reference', 'provider_status',
        'fee_amount', 'net_amount',
        'rejection_reason', 'admin_notes',
        'requested_at', 'reviewed_by', 'reviewed_at',
        'processed_at', 'completed_at', 'metadata',
    ];

    protected function casts(): array
    {
        return [
            'usd_amount'    => 'decimal:8',
            'local_amount'  => 'decimal:8',
            'exchange_rate' => 'decimal:6',
            'fee_amount'    => 'decimal:8',
            'net_amount'    => 'decimal:8',
            'metadata'      => 'array',
            'requested_at'  => 'datetime',
            'reviewed_at'   => 'datetime',
            'processed_at'  => 'datetime',
            'completed_at'  => 'datetime',
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

    public function isPending(): bool     { return $this->status === 'pending'; }
    public function isApproved(): bool    { return $this->status === 'approved'; }
    public function isProcessing(): bool  { return $this->status === 'processing'; }
    public function isSuccessful(): bool  { return $this->status === 'successful'; }
    public function isFailed(): bool      { return $this->status === 'failed'; }
    public function isRejected(): bool    { return $this->status === 'rejected'; }
    public function isCancelled(): bool   { return $this->status === 'cancelled'; }

    public function isTerminal(): bool
    {
        return in_array($this->status, ['successful', 'failed', 'rejected', 'cancelled']);
    }

    public function fundsShouldBeUnlocked(): bool
    {
        return in_array($this->status, ['failed', 'rejected', 'cancelled']);
    }

    public function getMethodLabelAttribute(): string
    {
        return match ($this->method) {
            'mpesa'      => 'M-Pesa',
            'usdt_trc20' => 'USDT (TRC20)',
            default      => ucfirst($this->method),
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'pending'    => 'amber',
            'approved'   => 'blue',
            'processing' => 'cyan',
            'successful' => 'emerald',
            'failed'     => 'red',
            'rejected'   => 'red',
            'cancelled'  => 'gray',
            default      => 'gray',
        };
    }
}
