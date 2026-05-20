<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BotInvestment extends Model
{
    protected $fillable = [
        'user_id', 'wallet_id', 'bot_plan_id', 'wallet_type',
        'principal_amount', 'daily_roi_percent', 'total_earned',
        'total_withdrawn', 'started_at', 'ends_at',
        'last_earning_at', 'status', 'metadata',
    ];

    protected function casts(): array
    {
        return [
            'principal_amount'  => 'decimal:2',
            'daily_roi_percent' => 'decimal:4',
            'total_earned'      => 'decimal:2',
            'total_withdrawn'   => 'decimal:2',
            'started_at'        => 'datetime',
            'ends_at'           => 'datetime',
            'last_earning_at'   => 'datetime',
            'metadata'          => 'array',
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

    public function botPlan(): BelongsTo
    {
        return $this->belongsTo(BotPlan::class);
    }

    public function earnings(): HasMany
    {
        return $this->hasMany(BotEarning::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isExpired(): bool
    {
        return $this->isActive() && $this->ends_at && $this->ends_at->isPast();
    }

    public function hasEarnedToday(): bool
    {
        return $this->earnings()
            ->whereDate('earning_date', today())
            ->exists();
    }

    public function getDailyEarningAmountAttribute(): float
    {
        return round((float)$this->principal_amount * (float)$this->daily_roi_percent / 100, 8);
    }

    public function getProgressPercentAttribute(): float
    {
        if (!$this->ends_at) {
            return 0;
        }
        $total   = $this->started_at->diffInDays($this->ends_at);
        $elapsed = $this->started_at->diffInDays(now());
        return $total > 0 ? min(100, round($elapsed / $total * 100, 1)) : 100;
    }

    public function getDaysRemainingAttribute(): ?int
    {
        if (!$this->ends_at) {
            return null;
        }
        return max(0, (int) now()->diffInDays($this->ends_at, false));
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'active'    => 'emerald',
            'completed' => 'cyan',
            'cancelled' => 'red',
            'paused'    => 'amber',
            default     => 'gray',
        };
    }
}
