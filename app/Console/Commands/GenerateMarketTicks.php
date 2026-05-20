<?php

namespace App\Console\Commands;

use App\Models\TradingAsset;
use App\Services\TradingEngineService;
use Illuminate\Console\Command;

class GenerateMarketTicks extends Command
{
    protected $signature   = 'market:ticks {--count=1 : Number of ticks to generate per asset}';
    protected $description = 'Generate simulated price ticks for all active trading assets';

    public function handle(TradingEngineService $engine): int
    {
        $assets = TradingAsset::where('is_active', true)->orderBy('sort_order')->get();

        if ($assets->isEmpty()) {
            $this->warn('No active trading assets found. Run the seeder first.');
            return self::SUCCESS;
        }

        $count = (int) $this->option('count');

        foreach ($assets as $asset) {
            $ticks = $engine->generateTicksForAsset($asset, $count);
            $latest = end($ticks);
            $this->line("  {$asset->symbol}: {$asset->formatPrice((float) $latest->price)} ({$latest->direction})");
        }

        $this->info("Generated {$count} tick(s) for {$assets->count()} asset(s).");

        return self::SUCCESS;
    }
}
