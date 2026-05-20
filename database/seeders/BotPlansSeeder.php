<?php

namespace Database\Seeders;

use App\Models\BotPlan;
use Illuminate\Database\Seeder;

class BotPlansSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            [
                'name'              => 'Conservative Bot',
                'slug'              => 'conservative-bot',
                'description'       => 'Low-risk steady daily returns. Ideal for cautious investors who want stable simulated growth.',
                'daily_roi_percent' => 1.0,
                'min_investment'    => 10.00,
                'max_investment'    => 1000.00,
                'duration_days'     => 30,
                'risk_level'        => 'low',
                'status'            => 'active',
                'sort_order'        => 1,
            ],
            [
                'name'              => 'Balanced Bot',
                'slug'              => 'balanced-bot',
                'description'       => 'Moderate risk with solid daily returns. The most popular choice for balanced portfolio growth.',
                'daily_roi_percent' => 2.0,
                'min_investment'    => 25.00,
                'max_investment'    => 5000.00,
                'duration_days'     => 30,
                'risk_level'        => 'medium',
                'status'            => 'active',
                'sort_order'        => 2,
            ],
            [
                'name'              => 'Aggressive Bot',
                'slug'              => 'aggressive-bot',
                'description'       => 'High-risk, high-reward strategy with elevated daily ROI for experienced users.',
                'daily_roi_percent' => 3.0,
                'min_investment'    => 50.00,
                'max_investment'    => 10000.00,
                'duration_days'     => 30,
                'risk_level'        => 'high',
                'status'            => 'active',
                'sort_order'        => 3,
            ],
            [
                'name'              => 'Extreme Scalper Bot',
                'slug'              => 'extreme-scalper-bot',
                'description'       => 'Maximum simulated returns for high-risk users. Very short duration, extreme daily ROI.',
                'daily_roi_percent' => 5.0,
                'min_investment'    => 100.00,
                'max_investment'    => 20000.00,
                'duration_days'     => 15,
                'risk_level'        => 'extreme',
                'status'            => 'inactive',
                'sort_order'        => 4,
            ],
        ];

        foreach ($plans as $plan) {
            BotPlan::updateOrCreate(['slug' => $plan['slug']], $plan);
        }

        $this->command->info('  Seeded 4 bot plans (3 active, 1 inactive).');
    }
}
