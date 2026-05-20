<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TradingAsset;
use App\Services\AdminLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminAssetController extends Controller
{
    public function __construct(private AdminLogService $logger) {}

    public function index(): View
    {
        $assets = TradingAsset::orderBy('sort_order')->orderBy('symbol')->get();
        return view('admin.assets.index', compact('assets'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateAsset($request);

        $asset = TradingAsset::create(array_merge($validated, [
            'current_price' => $validated['current_price'] ?? $validated['base_price'],
        ]));

        $this->logger->log(
            auth()->user(),
            'asset_created',
            TradingAsset::class,
            $asset->id,
            [],
            $validated
        );

        return back()->with('success', "Asset {$asset->symbol} created successfully.");
    }

    public function update(Request $request, TradingAsset $asset): RedirectResponse
    {
        $validated = $this->validateAsset($request, $asset->id);
        $old       = $asset->only(array_keys($validated));

        $asset->update($validated);

        $this->logger->log(
            auth()->user(),
            'asset_updated',
            TradingAsset::class,
            $asset->id,
            $old,
            $validated
        );

        return back()->with('success', "Asset {$asset->symbol} updated successfully.");
    }

    public function destroy(TradingAsset $asset): RedirectResponse
    {
        $symbol = $asset->symbol;

        // Prevent deleting asset with open trades
        if ($asset->trades()->where('status', 'open')->exists()) {
            return back()->with('error', "Cannot delete {$symbol}: it has open trades.");
        }

        $this->logger->log(
            auth()->user(),
            'asset_deleted',
            TradingAsset::class,
            $asset->id,
            $asset->toArray(),
            []
        );

        $asset->delete();

        return back()->with('success', "Asset {$symbol} deleted.");
    }

    public function toggle(TradingAsset $asset): RedirectResponse
    {
        $was = $asset->is_active;
        $asset->update(['is_active' => ! $was]);

        $this->logger->log(
            auth()->user(),
            $was ? 'asset_deactivated' : 'asset_activated',
            TradingAsset::class,
            $asset->id,
            ['is_active' => $was],
            ['is_active' => ! $was]
        );

        $state = $asset->is_active ? 'activated' : 'deactivated';
        return back()->with('success', "Asset {$asset->symbol} {$state}.");
    }

    private function validateAsset(Request $request, ?int $ignoreId = null): array
    {
        $symbolRule = 'required|string|max:20|unique:trading_assets,symbol'
            . ($ignoreId ? ",{$ignoreId}" : '');

        return $request->validate([
            'symbol'      => $symbolRule,
            'name'        => 'required|string|max:100',
            'type'        => 'required|in:forex,crypto,synthetic,stock',
            'base_price'  => 'required|numeric|gt:0',
            'current_price' => 'nullable|numeric|gt:0',
            'volatility'  => 'required|numeric|min:0',
            'trend_bias'  => 'required|in:bullish,bearish,neutral',
            'is_active'   => 'boolean',
            'sort_order'  => 'integer|min:0',
        ]);
    }
}
