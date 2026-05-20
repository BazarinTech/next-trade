<?php

namespace App\Services;

use App\Models\PriceTick;
use App\Models\Trade;
use App\Models\TradingAsset;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class TradingEngineService
{
    public const ALLOWED_EXPIRY       = [30, 60, 120, 300];
    public const DEFAULT_MIN_PROFIT   = 0.70;
    public const DEFAULT_MAX_PROFIT   = 0.95;

    public function __construct(
        private WalletService           $walletService,
        private SimulationConfigService $simConfig,
        private NotificationService     $notifier
    ) {}

    // ─── Price Generation ─────────────────────────────────────────────────────

    public function generateNextTick(TradingAsset $asset, ?\Carbon\Carbon $tickTime = null): PriceTick
    {
        $currentPrice = (float) $asset->current_price;
        $config       = $this->simConfig->getActiveConfig();

        // Apply volatility multiplier from active simulation config
        $baseVolatility = (float) $asset->volatility;
        $multiplier     = $config ? (float) $config->volatility_multiplier : 1.0;
        $volatility     = $baseVolatility * $multiplier;

        // Apply trend bias scaled by trend_strength
        $trendStrength = $config ? (float) $config->trend_strength : 0.5;
        $biasFactor    = match ($asset->trend_bias) {
            'bullish' => 0.0002 * $trendStrength,
            'bearish' => -0.0002 * $trendStrength,
            default   => 0.0,
        };

        $z             = $this->randomNormal();
        $changePercent = ($z * $volatility) + $biasFactor;
        $newPrice      = $currentPrice * (1.0 + $changePercent);

        // Clamp to sane range (10%–500% of base)
        $base     = (float) $asset->base_price;
        $newPrice = max($base * 0.10, min($base * 5.0, $newPrice));
        $newPrice = max(0.00000001, $newPrice);

        $precision = $this->pricePrecision($asset);
        $newPrice  = round($newPrice, $precision);

        $direction = match (true) {
            $newPrice > $currentPrice => 'up',
            $newPrice < $currentPrice => 'down',
            default                   => 'flat',
        };

        $tick = PriceTick::create([
            'trading_asset_id' => $asset->id,
            'price'            => $newPrice,
            'previous_price'   => $currentPrice,
            'direction'        => $direction,
            'tick_time'        => $tickTime ?? now(),
        ]);

        $asset->update(['current_price' => $newPrice]);

        return $tick;
    }

    public function generateTicksForAsset(TradingAsset $asset, int $count = 1): array
    {
        $ticks = [];
        if ($count === 1) {
            $ticks[] = $this->generateNextTick($asset);
            return $ticks;
        }

        // Backfill: space ticks 3 seconds apart ending at now
        $startOffset = ($count - 1) * 3;
        for ($i = 0; $i < $count; $i++) {
            $tickTime    = now()->subSeconds($startOffset - ($i * 3));
            $ticks[]     = $this->generateNextTick($asset, $tickTime);
        }
        return $ticks;
    }

    // ─── Trade Placement ──────────────────────────────────────────────────────

    public function placeTrade(
        User         $user,
        TradingAsset $asset,
        string       $direction,
        float|string $amount,
        int          $expirySeconds,
        string       $walletType
    ): Trade {
        if (! in_array($expirySeconds, self::ALLOWED_EXPIRY, true)) {
            throw new RuntimeException('Invalid expiry. Allowed: 30, 60, 120, 300 seconds.');
        }
        if (! in_array($direction, ['buy', 'sell'], true)) {
            throw new RuntimeException('Direction must be buy or sell.');
        }
        if (! $asset->is_active) {
            throw new RuntimeException('This asset is not currently available for trading.');
        }

        return DB::transaction(function () use ($user, $asset, $direction, $amount, $expirySeconds, $walletType) {
            $wallet = $user->wallets()->where('type', $walletType)->firstOrFail();

            $this->walletService->debit(
                $wallet,
                $amount,
                'trade_loss',
                "Trade opened: {$asset->symbol} {$direction} @ {$asset->formatPrice()}"
            );

            $now = now();

            return Trade::create([
                'user_id'          => $user->id,
                'wallet_id'        => $wallet->id,
                'trading_asset_id' => $asset->id,
                'wallet_type'      => $walletType,
                'direction'        => $direction,
                'stake_amount'     => $amount,
                'entry_price'      => $asset->current_price,
                'expiry_seconds'   => $expirySeconds,
                'opened_at'        => $now,
                'expires_at'       => $now->copy()->addSeconds($expirySeconds),
                'status'           => 'open',
            ]);
        });
    }

    // ─── Trade Settlement ─────────────────────────────────────────────────────

    public function settleTrade(Trade $trade): Trade
    {
        return DB::transaction(function () use ($trade) {
            $locked = Trade::where('id', $trade->id)->lockForUpdate()->first();

            if ($locked->status !== 'open') {
                return $locked;
            }

            $asset  = $locked->tradingAsset;
            $config = $this->simConfig->getActiveConfig();

            // 1. Generate a normal market tick (chart continuity)
            $this->generateNextTick($asset);
            $asset->refresh();

            // 2. Determine outcome via win probability — server-side, opaque to frontend
            $winProbability = $config ? (float) $config->win_probability / 100 : 0.5;
            $isWin          = (mt_rand() / mt_getrandmax()) < $winProbability;

            // 3. Derive a settlement price that confirms the outcome
            $entryPrice   = (float) $locked->entry_price;
            $currentPrice = (float) $asset->current_price;
            $baseVol      = (float) $asset->volatility;
            $volMult      = $config ? (float) $config->volatility_multiplier : 1.0;

            // Small realistic pip movement in the required direction
            $pip = max(
                $currentPrice * 0.000001,
                $currentPrice * ($baseVol * $volMult) * 0.1
            );

            $shouldPriceGoUp = ($isWin && $locked->direction === 'buy')
                || (! $isWin && $locked->direction === 'sell');

            $exitPrice = $shouldPriceGoUp
                ? $entryPrice + $pip
                : $entryPrice - $pip;

            $exitPrice = max(0.00000001, round($exitPrice, $this->pricePrecision($asset)));

            // 4. Calculate displacement and profit
            $stake        = (float) $locked->stake_amount;
            $displacement = $this->calculateDisplacement($entryPrice, $exitPrice);

            $minRate = $config ? (float) $config->min_profit_multiplier : self::DEFAULT_MIN_PROFIT;
            $maxRate = $config ? (float) $config->max_profit_multiplier : self::DEFAULT_MAX_PROFIT;

            $baseProfit = $stake * $displacement * 100;
            $profit     = max($stake * $minRate, min($baseProfit, $stake * $maxRate));

            $status = $isWin ? 'won' : 'lost';

            [$profitLoss, $payout] = match ($status) {
                'won'  => [$profit, $stake + $profit],
                'lost' => [-$stake, 0.0],
            };

            // 5. Persist trade result
            $locked->exit_price   = $exitPrice;
            $locked->displacement = $displacement;
            $locked->profit_loss  = $profitLoss;
            $locked->payout       = $payout;
            $locked->status       = $status;
            $locked->closed_at    = now();
            $locked->save();

            // 6. Update wallet
            $wallet = $locked->wallet;

            if ($status === 'won') {
                $this->walletService->credit(
                    $wallet,
                    (string) $payout,
                    'trade_profit',
                    "Trade won: {$asset->symbol} {$locked->direction} +\$" . number_format($profit, 2),
                    ['trade_id' => $locked->id]
                );
            }
            // Loss: stake already debited on placement — nothing more to do

            // Notify user of trade outcome
            $user = $locked->wallet->user;
            if ($status === 'won') {
                $this->notifier->send($user, 'trade_won', 'Trade Won! 🎉',
                    "Your {$asset->symbol} {$locked->direction} trade won +\$" . number_format($profit, 2) . '.',
                    ['trade_id' => $locked->id, 'profit' => $profit]);
            } else {
                $this->notifier->send($user, 'trade_lost', 'Trade Closed',
                    "Your {$asset->symbol} {$locked->direction} trade closed at a loss of \$" . number_format($stake, 2) . '.',
                    ['trade_id' => $locked->id, 'loss' => $stake]);
            }

            return $locked->fresh();
        });
    }

    // ─── Queries ──────────────────────────────────────────────────────────────

    public function getActiveTrades(User $user): Collection
    {
        return $user->trades()
            ->where('status', 'open')
            ->with('tradingAsset')
            ->orderByDesc('opened_at')
            ->get();
    }

    public function getMarketSnapshot(): Collection
    {
        return TradingAsset::where('is_active', true)
            ->orderBy('sort_order')
            ->get();
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    public function calculateDisplacement(float $entryPrice, float $exitPrice): float
    {
        if ($entryPrice == 0) {
            return 0.0;
        }
        return abs($exitPrice - $entryPrice) / $entryPrice;
    }

    public function calculateProfitLoss(Trade $trade, float $exitPrice): array
    {
        $entry        = (float) $trade->entry_price;
        $stake        = (float) $trade->stake_amount;
        $displacement = $this->calculateDisplacement($entry, $exitPrice);
        $config       = $this->simConfig->getActiveConfig();

        $priceDiff = $exitPrice - $entry;
        $threshold = $entry * 0.000001;

        if (abs($priceDiff) < $threshold) {
            $status = 'draw';
        } elseif ($trade->direction === 'buy') {
            $status = $priceDiff > 0 ? 'won' : 'lost';
        } else {
            $status = $priceDiff < 0 ? 'won' : 'lost';
        }

        $minRate = $config ? (float) $config->min_profit_multiplier : self::DEFAULT_MIN_PROFIT;
        $maxRate = $config ? (float) $config->max_profit_multiplier : self::DEFAULT_MAX_PROFIT;

        $baseProfit = $stake * $displacement * 100;
        $profit     = max($stake * $minRate, min($baseProfit, $stake * $maxRate));

        return match ($status) {
            'won'  => ['status' => 'won',  'profit_loss' => $profit,  'payout' => $stake + $profit, 'displacement' => $displacement],
            'lost' => ['status' => 'lost', 'profit_loss' => -$stake,  'payout' => 0.0,              'displacement' => $displacement],
            'draw' => ['status' => 'draw', 'profit_loss' => 0.0,      'payout' => $stake,            'displacement' => $displacement],
        };
    }

    private function pricePrecision(TradingAsset $asset): int
    {
        return match ($asset->type) {
            'forex'  => 5,
            'crypto' => (float) $asset->base_price >= 1000 ? 2 : 6,
            default  => 2,
        };
    }

    private function randomNormal(): float
    {
        do {
            $u1 = mt_rand(1, PHP_INT_MAX) / PHP_INT_MAX;
            $u2 = mt_rand(1, PHP_INT_MAX) / PHP_INT_MAX;
        } while ($u1 <= PHP_FLOAT_EPSILON);

        return sqrt(-2.0 * log($u1)) * cos(2.0 * M_PI * $u2);
    }
}
