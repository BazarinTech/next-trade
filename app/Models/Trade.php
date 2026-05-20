<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Trade extends Model
{
    protected $fillable = [
        'user_id', 'wallet_id', 'trading_asset_id', 'wallet_type',
        'direction', 'stake_amount', 'entry_price', 'exit_price',
        'displacement', 'profit_loss', 'payout', 'expiry_seconds',
        'opened_at', 'expires_at', 'closed_at', 'status', 'metadata',
    ];

    protected function casts(): array
    {
        return [
            'stake_amount' => 'decimal:8',
            'entry_price'  => 'decimal:8',
            'exit_price'   => 'decimal:8',
            'displacement' => 'decimal:8',
            'profit_loss'  => 'decimal:8',
            'payout'       => 'decimal:8',
            'opened_at'    => 'datetime',
            'expires_at'   => 'datetime',
            'closed_at'    => 'datetime',
            'metadata'     => 'array',
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

    public function tradingAsset(): BelongsTo
    {
        return $this->belongsTo(TradingAsset::class);
    }

    public function isOpen(): bool
    {
        return $this->status === 'open';
    }

    public function isExpired(): bool
    {
        return $this->isOpen() && $this->expires_at->isPast();
    }

    public function getTimeRemainingAttribute(): int
    {
        if (! $this->isOpen()) {
            return 0;
        }
        return max(0, (int) now()->diffInSeconds($this->expires_at, false));
    }

    public function getResultColorAttribute(): string
    {
        return match ($this->status) {
            'won'  => 'emerald',
            'lost' => 'red',
            'draw' => 'amber',
            default => 'gray',
        };
    }

    public function getResultIconAttribute(): string
    {
        return match ($this->status) {
            'won'  => 'trending-up',
            'lost' => 'trending-down',
            'draw' => 'minus',
            default => 'clock',
        };
    }
}
