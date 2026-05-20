<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TradingAsset extends Model
{
    protected $fillable = [
        'symbol', 'name', 'type', 'base_price', 'current_price',
        'volatility', 'trend_bias', 'is_active', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'base_price'    => 'decimal:8',
            'current_price' => 'decimal:8',
            'volatility'    => 'decimal:6',
            'is_active'     => 'boolean',
        ];
    }

    public function trades(): HasMany
    {
        return $this->hasMany(Trade::class);
    }

    public function priceTicks(): HasMany
    {
        return $this->hasMany(PriceTick::class);
    }

    public function latestTicks(int $limit = 100)
    {
        return $this->priceTicks()
            ->orderByDesc('tick_time')
            ->limit($limit)
            ->get()
            ->reverse()
            ->values();
    }

    public function getPriceChangeAttribute(): string
    {
        $diff = bcsub((string) $this->current_price, (string) $this->base_price, 8);
        if (bccomp((string) $this->base_price, '0', 8) === 0) {
            return '0.0000';
        }
        return bcmul(bcdiv($diff, (string) $this->base_price, 10), '100', 4);
    }

    public function formatPrice(string|float|null $price = null): string
    {
        $p = (float) ($price ?? $this->current_price);
        return match ($this->type) {
            'forex'     => number_format($p, 5),
            'crypto'    => $p >= 1000
                ? '$' . number_format($p, 2)
                : '$' . number_format($p, 4),
            'synthetic' => '$' . number_format($p, 2),
            'stock'     => '$' . number_format($p, 2),
            default     => number_format($p, 2),
        };
    }

    public function getTypeColorAttribute(): string
    {
        return match ($this->type) {
            'crypto'    => 'amber',
            'forex'     => 'cyan',
            'synthetic' => 'purple',
            'stock'     => 'emerald',
            default     => 'gray',
        };
    }
}
