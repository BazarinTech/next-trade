<?php

namespace App\Console\Commands;

use App\Models\TradingAsset;
use App\Services\TradingEngineService;
use Illuminate\Console\Command;

class MarketTicksDaemon extends Command
{
    protected $signature   = 'market:ticks-daemon {--interval=3 : Seconds between tick batches}';
    protected $description = 'Continuously generate price ticks for all active assets (run via supervisor or nohup)';

    public function handle(TradingEngineService $engine): int
    {
        $interval = max(1, (int) $this->option('interval'));
        $this->info("Tick daemon started — interval: {$interval}s. PID: " . getmypid());

        pcntl_async_signals(true);
        $running = true;
        pcntl_signal(SIGTERM, static function () use (&$running) { $running = false; });
        pcntl_signal(SIGINT,  static function () use (&$running) { $running = false; });

        while ($running) {
            $start = microtime(true);

            try {
                $assets = TradingAsset::where('is_active', true)->orderBy('sort_order')->get();
                foreach ($assets as $asset) {
                    if (! $running) break;
                    $engine->generateNextTick($asset);
                }
            } catch (\Throwable $e) {
                $this->error('Tick error: ' . $e->getMessage());
            }

            $elapsed  = microtime(true) - $start;
            $sleepUs  = (int) (max(0.0, $interval - $elapsed) * 1_000_000);
            if ($sleepUs > 0) {
                usleep($sleepUs);
            }
        }

        $this->info('Tick daemon stopped.');
        return self::SUCCESS;
    }
}
