<?php

namespace App\Http\Controllers;

use App\Models\TradingAsset;
use App\Services\SimulationConfigService;
use App\Services\TradingEngineService;
use Illuminate\Http\JsonResponse;

class MarketController extends Controller
{
    public function __construct(
        private TradingEngineService    $engine,
        private SimulationConfigService $simConfig
    ) {}

    public function snapshot(): JsonResponse
    {
        $config = $this->simConfig->getActiveConfig();

        $assets = TradingAsset::where('is_active', true)
            ->orderBy('sort_order')
            ->get()
            ->map(fn (TradingAsset $a) => [
                'id'           => $a->id,
                'symbol'       => $a->symbol,
                'name'         => $a->name,
                'type'         => $a->type,
                'price'        => (float) $a->current_price,
                'base_price'   => (float) $a->base_price,
                'change_pct'   => (float) $a->price_change,
                'color'        => $a->type_color,
            ]);

        return response()->json([
            'assets'       => $assets,
            'candle_speed' => $config?->candle_speed_seconds ?? 5,
            'difficulty'   => $config?->difficulty ?? 'normal',
        ]);
    }

    public function ticks(TradingAsset $asset): JsonResponse
    {
        // Seed initial history if table is empty
        if ($asset->priceTicks()->count() < 2) {
            $this->engine->generateTicksForAsset($asset, 60);
            $asset->refresh();
        }

        // Generate a new live tick if the latest is more than 2 s old
        $latest = $asset->priceTicks()->latest('tick_time')->first();
        if (! $latest || $latest->tick_time->lt(now()->subSeconds(2))) {
            $this->engine->generateNextTick($asset);
        }

        // Prune ticks older than 30 minutes to keep the table lean
        $asset->priceTicks()
            ->where('tick_time', '<', now()->subMinutes(30))
            ->delete();

        // Return the 300 most recent ticks in ascending order for the chart
        $ticks = $asset->priceTicks()
            ->orderByDesc('tick_time')
            ->limit(300)
            ->get()
            ->sortBy('tick_time')
            ->values()
            ->map(fn ($t) => [
                'price'     => (float) $t->price,
                'direction' => $t->direction,
                'time'      => $t->tick_time->toISOString(),
            ]);

        return response()->json($ticks);
    }
}
