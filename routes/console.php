<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('market:ticks')->everyMinute();
Schedule::command('trades:settle')->everyMinute();
Schedule::command('bots:process-earnings')->dailyAt('00:05');
Schedule::command('nexttrade:cleanup-pending')->daily();
// nexttrade:reconcile — run manually or via cron as needed
