<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\BotEarning;
use App\Models\BotInvestment;
use App\Models\PaymentDeposit;
use App\Models\Trade;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Withdrawal;

class Wallet extends Model
{
    protected $fillable = [
        'user_id',
        'type',
        'currency',
        'balance',
        'locked_balance',
        'total_deposited',
        'total_withdrawn',
        'total_profit',
        'total_loss',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'balance'         => 'decimal:8',
            'locked_balance'  => 'decimal:8',
            'total_deposited' => 'decimal:8',
            'total_withdrawn' => 'decimal:8',
            'total_profit'    => 'decimal:8',
            'total_loss'      => 'decimal:8',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function trades(): HasMany
    {
        return $this->hasMany(Trade::class);
    }

    public function botInvestments(): HasMany
    {
        return $this->hasMany(BotInvestment::class);
    }

    public function botEarnings(): HasMany
    {
        return $this->hasMany(BotEarning::class);
    }

    public function paymentDeposits(): HasMany
    {
        return $this->hasMany(PaymentDeposit::class);
    }

    public function withdrawals(): HasMany
    {
        return $this->hasMany(Withdrawal::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isFrozen(): bool
    {
        return $this->status === 'frozen';
    }

    public function isDemo(): bool
    {
        return $this->type === 'demo';
    }

    public function isLive(): bool
    {
        return $this->type === 'live';
    }

    /** Available balance = balance - locked */
    public function getAvailableBalanceAttribute(): string
    {
        return bcsub((string) $this->balance, (string) $this->locked_balance, 8);
    }

    /** Net P&L */
    public function getNetPnlAttribute(): string
    {
        return bcsub((string) $this->total_profit, (string) $this->total_loss, 8);
    }

    public function getTypeLabel(): string
    {
        return ucfirst($this->type);
    }
}
