<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    protected $fillable = [
        'user_id',
        'wallet_id',
        'type',
        'amount',
        'currency',
        'balance_before',
        'balance_after',
        'reference',
        'description',
        'status',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'amount'         => 'decimal:8',
            'balance_before' => 'decimal:8',
            'balance_after'  => 'decimal:8',
            'metadata'       => 'array',
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

    public function isCredit(): bool
    {
        return in_array($this->type, ['deposit', 'trade_profit', 'bot_profit', 'adjustment']);
    }

    public function isDebit(): bool
    {
        return in_array($this->type, ['withdrawal', 'trade_loss', 'bot_investment']);
    }

    public function getTypeLabel(): string
    {
        return match ($this->type) {
            'deposit'        => 'Deposit',
            'withdrawal'     => 'Withdrawal',
            'trade_profit'   => 'Trade Profit',
            'trade_loss'     => 'Trade Loss',
            'bot_profit'     => 'Bot Profit',
            'bot_investment' => 'Bot Investment',
            'adjustment'     => 'Adjustment',
            default          => ucfirst($this->type),
        };
    }

    public function getStatusColor(): string
    {
        return match ($this->status) {
            'successful' => 'emerald',
            'pending'    => 'amber',
            'failed'     => 'red',
            'cancelled'  => 'gray',
            default      => 'gray',
        };
    }

    public function getSignedAmount(): string
    {
        $sign = $this->isCredit() ? '+' : '-';
        return $sign . number_format((float) $this->amount, 2);
    }
}
