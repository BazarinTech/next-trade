<?php

namespace App\Jobs;

use App\Models\Trade;
use App\Services\TradingEngineService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class SettleTradeJob implements ShouldQueue
{
    use Queueable;

    public int $tries   = 3;
    public int $timeout = 30;

    public function __construct(public readonly int $tradeId) {}

    public function handle(TradingEngineService $engine): void
    {
        $trade = Trade::find($this->tradeId);
        if (!$trade || $trade->status !== 'open') {
            return;
        }

        try {
            $engine->settleTrade($trade);
        } catch (\Exception $e) {
            Log::error("SettleTradeJob failed for trade #{$this->tradeId}: " . $e->getMessage());
            throw $e;
        }
    }
}
