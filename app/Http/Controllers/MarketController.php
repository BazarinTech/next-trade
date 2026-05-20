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
        // Auto-seed initial ticks if none exist yet
        if ($asset->priceTicks()->count() < 2) {
            $this->engine->generateTicksForAsset($asset, 60);
            $asset->refresh();
        }

        $ticks = $asset->priceTicks()
            ->orderByDesc('tick_time')
            ->limit(120)
            ->get()
            ->reverse()
            ->values()
            ->map(fn ($t) => [
                'price'     => (float) $t->price,
                'direction' => $t->direction,
                'time'      => $t->tick_time->toISOString(),
            ]);

        return response()->json($ticks);
    }
}
