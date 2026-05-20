<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PriceTick extends Model
{
    protected $fillable = [
        'trading_asset_id', 'price', 'previous_price', 'direction', 'tick_time', 'metadata',
    ];

    protected function casts(): array
    {
        return [
            'price'          => 'decimal:8',
            'previous_price' => 'decimal:8',
            'tick_time'      => 'datetime',
            'metadata'       => 'array',
        ];
    }

    public function tradingAsset(): BelongsTo
    {
        return $this->belongsTo(TradingAsset::class);
    }
}
