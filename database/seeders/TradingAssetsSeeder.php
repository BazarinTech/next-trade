<?php

namespace Database\Seeders;

use App\Models\TradingAsset;
use App\Services\TradingEngineService;
use Illuminate\Database\Seeder;

class TradingAssetsSeeder extends Seeder
{
    public function run(TradingEngineService $engine): void
    {
        $assets = [
            [
                'symbol'      => 'BTC/USD',
                'name'        => 'Bitcoin',
                'type'        => 'crypto',
                'base_price'  => 65000.00,
                'volatility'  => 0.012,
                'trend_bias'  => 'bullish',
                'sort_order'  => 1,
            ],
            [
                'symbol'      => 'ETH/USD',
                'name'        => 'Ethereum',
                'type'        => 'crypto',
                'base_price'  => 3500.00,
                'volatility'  => 0.010,
                'trend_bias'  => 'bullish',
                'sort_order'  => 2,
            ],
            [
                'symbol'      => 'EUR/USD',
                'name'        => 'Euro / US Dollar',
                'type'        => 'forex',
                'base_price'  => 1.08500,
                'volatility'  => 0.0003,
                'trend_bias'  => 'neutral',
                'sort_order'  => 3,
            ],
            [
                'symbol'      => 'GBP/USD',
                'name'        => 'British Pound / US Dollar',
                'type'        => 'forex',
                'base_price'  => 1.26500,
                'volatility'  => 0.0004,
                'trend_bias'  => 'neutral',
                'sort_order'  => 4,
            ],
            [
                'symbol'      => 'XAU/USD',
                'name'        => 'Gold / US Dollar',
                'type'        => 'synthetic',
                'base_price'  => 2350.00,
                'volatility'  => 0.003,
                'trend_bias'  => 'bullish',
                'sort_order'  => 5,
            ],
            [
                'symbol'      => 'VOLTEX',
                'name'        => 'Voltex Synthetic Index',
                'type'        => 'synthetic',
                'base_price'  => 1000.00,
                'volatility'  => 0.025,
                'trend_bias'  => 'neutral',
                'sort_order'  => 6,
            ],
        ];

        foreach ($assets as $data) {
            $asset = TradingAsset::updateOrCreate(
                ['symbol' => $data['symbol']],
                array_merge($data, ['current_price' => $data['base_price'], 'is_active' => true])
            );

            // Generate 60 initial history ticks spread over the last 6 minutes
            $this->generateHistoryTicks($engine, $asset, 60);

            $this->command?->line("  Seeded {$asset->symbol} with 60 initial ticks @ {$asset->formatPrice()}");
        }
    }

    private function generateHistoryTicks(TradingEngineService $engine, TradingAsset $asset, int $count): void
    {
        // Generate ticks quickly (no time spread needed for chart data)
        $engine->generateTicksForAsset($asset, $count);
    }
}
