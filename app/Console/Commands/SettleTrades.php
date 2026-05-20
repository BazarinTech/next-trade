<?php

namespace App\Console\Commands;

use App\Models\Trade;
use App\Services\TradingEngineService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SettleTrades extends Command
{
    protected $signature   = 'trades:settle';
    protected $description = 'Settle all open trades that have expired';

    public function handle(TradingEngineService $engine): int
    {
        $trades = Trade::where('status', 'open')
            ->where('expires_at', '<=', now())
            ->with(['wallet', 'tradingAsset'])
            ->get();

        if ($trades->isEmpty()) {
            $this->line('No expired trades to settle.');
            return self::SUCCESS;
        }

        $this->info("Settling {$trades->count()} expired trade(s)...");

        $won = $lost = $draw = $failed = 0;

        foreach ($trades as $trade) {
            try {
                $settled = $engine->settleTrade($trade);
                match ($settled->status) {
                    'won'  => $won++,
                    'lost' => $lost++,
                    'draw' => $draw++,
                    default => null,
                };
                $this->line("  [#{$trade->id}] {$trade->tradingAsset->symbol} {$trade->direction} → {$settled->status} (stake: \${$trade->stake_amount})");
            } catch (\Throwable $e) {
                $failed++;
                $this->error("  [#{$trade->id}] Failed: {$e->getMessage()}");
                Log::error('Trade settlement failed', [
                    'trade_id' => $trade->id,
                    'error'    => $e->getMessage(),
                ]);
            }
        }

        $this->info("Done — Won: {$won} | Lost: {$lost} | Draw: {$draw} | Failed: {$failed}");

        return self::SUCCESS;
    }
}
