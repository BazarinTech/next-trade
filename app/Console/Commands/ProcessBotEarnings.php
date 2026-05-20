<?php

namespace App\Console\Commands;

use App\Services\BotInvestmentService;
use Illuminate\Console\Command;

class ProcessBotEarnings extends Command
{
    protected $signature   = 'bots:process-earnings';
    protected $description = 'Process daily earnings for all active bot investments';

    public function handle(BotInvestmentService $botService): int
    {
        $this->info('Processing bot earnings...');

        $result = $botService->processDailyEarnings();

        $this->info("  Processed : {$result['processed']}");
        $this->info("  Skipped   : {$result['skipped']} (already earned today)");
        $this->info("  Completed : {$result['completed']} (expired investments)");

        if ($result['failed'] > 0) {
            $this->warn("  Failed    : {$result['failed']} (check logs)");
        }

        $this->info('Done.');

        return self::SUCCESS;
    }
}
