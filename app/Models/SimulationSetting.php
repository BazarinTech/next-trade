<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SimulationSetting extends Model
{
    protected $fillable = [
        'name', 'difficulty', 'win_probability', 'volatility_multiplier',
        'trend_strength', 'max_profit_multiplier', 'min_profit_multiplier',
        'max_loss_multiplier', 'candle_speed_seconds', 'is_active', 'metadata',
    ];

    protected function casts(): array
    {
        return [
            'win_probability'       => 'decimal:2',
            'volatility_multiplier' => 'decimal:4',
            'trend_strength'        => 'decimal:4',
            'max_profit_multiplier' => 'decimal:4',
            'min_profit_multiplier' => 'decimal:4',
            'max_loss_multiplier'   => 'decimal:4',
            'is_active'             => 'boolean',
            'metadata'              => 'array',
        ];
    }

    public function isActive(): bool
    {
        return $this->is_active;
    }

    public function getDifficultyColorAttribute(): string
    {
        return match ($this->difficulty) {
            'easy'    => 'emerald',
            'normal'  => 'cyan',
            'hard'    => 'amber',
            'extreme' => 'red',
            default   => 'gray',
        };
    }

    public function getDifficultyLabelAttribute(): string
    {
        return ucfirst($this->difficulty);
    }

    public function getPayoutRangeAttribute(): string
    {
        $min = (float) $this->min_profit_multiplier * 100;
        $max = (float) $this->max_profit_multiplier * 100;
        return "{$min}% – {$max}%";
    }
}
