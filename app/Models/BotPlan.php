<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BotPlan extends Model
{
    protected $fillable = [
        'name', 'slug', 'description', 'daily_roi_percent',
        'min_investment', 'max_investment', 'duration_days',
        'risk_level', 'status', 'sort_order', 'metadata',
    ];

    protected function casts(): array
    {
        return [
            'daily_roi_percent' => 'decimal:4',
            'min_investment'    => 'decimal:2',
            'max_investment'    => 'decimal:2',
            'duration_days'     => 'integer',
            'sort_order'        => 'integer',
            'metadata'          => 'array',
        ];
    }

    public function investments(): HasMany
    {
        return $this->hasMany(BotInvestment::class);
    }

    public function earnings(): HasMany
    {
        return $this->hasMany(BotEarning::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function getRiskColorAttribute(): string
    {
        return match ($this->risk_level) {
            'low'     => 'emerald',
            'medium'  => 'cyan',
            'high'    => 'amber',
            'extreme' => 'red',
            default   => 'gray',
        };
    }

    public function getRiskLabelAttribute(): string
    {
        return ucfirst($this->risk_level);
    }

    public function getDurationLabelAttribute(): string
    {
        return $this->duration_days ? "{$this->duration_days} days" : 'Unlimited';
    }

    public function getTotalEarningAttribute(): ?string
    {
        if (!$this->duration_days) {
            return null;
        }
        return number_format((float)$this->daily_roi_percent * $this->duration_days, 2) . '% total';
    }

    public function estimateEarnings(float $amount): array
    {
        $daily = $amount * (float)$this->daily_roi_percent / 100;
        $total = $this->duration_days ? $daily * $this->duration_days : null;

        return [
            'daily' => $daily,
            'total' => $total,
        ];
    }

    public function activeInvestmentsCount(): int
    {
        return $this->investments()->where('status', 'active')->count();
    }

    public function totalPrincipal(): float
    {
        return (float) $this->investments()->sum('principal_amount');
    }

    public function totalEarnedAll(): float
    {
        return (float) $this->investments()->sum('total_earned');
    }

    public function activeUsersCount(): int
    {
        return $this->investments()
            ->where('status', 'active')
            ->distinct('user_id')
            ->count('user_id');
    }
}
