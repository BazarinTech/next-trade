<?php

namespace App\Jobs;

use App\Models\BotEarning;
use App\Services\BotInvestmentService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class ProcessBotEarningJob implements ShouldQueue
{
    use Queueable;

    public int $tries   = 3;
    public int $timeout = 60;

    public function __construct(public readonly int $earningId) {}

    public function handle(BotInvestmentService $service): void
    {
        $earning = BotEarning::find($this->earningId);
        if (!$earning || $earning->status !== 'pending') {
            return;
        }

        try {
            $service->creditEarning($earning);
        } catch (\Exception $e) {
            Log::error("ProcessBotEarningJob failed for earning #{$this->earningId}: " . $e->getMessage());
            throw $e;
        }
    }
}
