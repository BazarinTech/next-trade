<?php

namespace Database\Seeders;

use App\Models\BotInvestment;
use App\Models\BotPlan;
use App\Models\Notification;
use App\Models\PaymentDeposit;
use App\Models\Trade;
use App\Models\TradingAsset;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use App\Models\Withdrawal;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Seeding demo data...');

        // ── Demo Users ────────────────────────────────────────────────────────
        $demoUsers = [
            ['name' => 'Alice Wambui',   'email' => 'alice@demo.nexttrade.dev',   'country' => 'Kenya',         'phone' => '0701000001'],
            ['name' => 'Bob Otieno',     'email' => 'bob@demo.nexttrade.dev',     'country' => 'Kenya',         'phone' => '0701000002'],
            ['name' => 'Carol Mutua',    'email' => 'carol@demo.nexttrade.dev',   'country' => 'Kenya',         'phone' => '0701000003'],
            ['name' => 'David Kamau',    'email' => 'david@demo.nexttrade.dev',   'country' => 'Uganda',        'phone' => '0701000004'],
            ['name' => 'Eve Njeri',      'email' => 'eve@demo.nexttrade.dev',     'country' => 'Tanzania',      'phone' => '0701000005'],
        ];

        $users = [];
        foreach ($demoUsers as $data) {
            $user = User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'name'     => $data['name'],
                    'username' => strtolower(explode(' ', $data['name'])[0]) . rand(10, 99),
                    'phone'    => $data['phone'],
                    'country'  => $data['country'],
                    'password' => Hash::make('password123'),
                    'is_admin' => false,
                    'is_banned'=> false,
                ]
            );

            // Ensure wallets
            $live = Wallet::firstOrCreate(
                ['user_id' => $user->id, 'type' => 'live'],
                ['balance' => rand(100, 2000) + 0.00, 'locked_balance' => 0]
            );
            $demo = Wallet::firstOrCreate(
                ['user_id' => $user->id, 'type' => 'demo'],
                ['balance' => 10000.00, 'locked_balance' => 0]
            );

            $users[] = compact('user', 'live', 'demo');
        }

        $this->command->info('  ✓ ' . count($users) . ' demo users created.');

        // ── Sample Trades ─────────────────────────────────────────────────────
        $asset = TradingAsset::first();
        if ($asset) {
            $statuses   = ['won', 'lost', 'won', 'won', 'lost'];
            $directions = ['buy', 'sell', 'buy', 'sell', 'buy'];
            foreach ($users as $i => $u) {
                $stake  = rand(10, 100);
                $status = $statuses[$i % count($statuses)];
                $pl     = $status === 'won' ? round($stake * 0.85, 2) : -$stake;

                $openedAt = now()->subMinutes(rand(2, 120));
                Trade::create([
                    'user_id'          => $u['user']->id,
                    'wallet_id'        => $u['demo']->id,
                    'trading_asset_id' => $asset->id,
                    'direction'        => $directions[$i % count($directions)],
                    'stake_amount'     => $stake,
                    'entry_price'      => 1.0800 + (rand(-50, 50) / 10000),
                    'exit_price'       => 1.0820 + (rand(-50, 50) / 10000),
                    'expiry_seconds'   => 60,
                    'profit_loss'      => $pl,
                    'payout'           => $status === 'won' ? $stake + abs($pl) : 0,
                    'status'           => $status,
                    'opened_at'        => $openedAt,
                    'expires_at'       => $openedAt->copy()->addMinute(),
                    'closed_at'        => $openedAt->copy()->addMinute(),
                ]);
            }
            $this->command->info('  ✓ Sample trades created.');
        }

        // ── Sample Bot Investments ────────────────────────────────────────────
        $plan = BotPlan::where('status', 'active')->first();
        if ($plan) {
            foreach (array_slice($users, 0, 3) as $u) {
                BotInvestment::firstOrCreate(
                    ['user_id' => $u['user']->id, 'bot_plan_id' => $plan->id, 'wallet_id' => $u['demo']->id],
                    [
                        'wallet_type'       => 'demo',
                        'principal_amount'  => 500,
                        'daily_roi_percent' => $plan->daily_roi_percent ?? 0.85,
                        'status'            => 'active',
                        'total_earned'      => round(rand(5, 50) + 0.0, 2),
                        'started_at'        => now()->subDays(rand(1, 5)),
                        'ends_at'           => now()->addDays(rand(5, 25)),
                    ]
                );
            }
            $this->command->info('  ✓ Sample bot investments created.');
        }

        // ── Sample Deposits ───────────────────────────────────────────────────
        foreach (array_slice($users, 0, 3) as $u) {
            PaymentDeposit::firstOrCreate(
                ['user_id' => $u['user']->id, 'method' => 'mpesa_stk', 'status' => 'successful'],
                [
                    'wallet_id'    => $u['live']->id,
                    'wallet_type'  => 'live',
                    'local_amount' => 1300,
                    'usd_amount'   => 10.00,
                    'exchange_rate'      => 130,
                    'account_reference' => 'DEMO' . $u['user']->id,
                    'phone'             => $u['user']->phone,
                    'credited_at'       => now()->subDays(rand(1, 10)),
                    'provider_transaction_id' => 'MPESA' . strtoupper(uniqid()),
                ]
            );
        }
        $this->command->info('  ✓ Sample deposits created.');

        // ── Sample Withdrawal ─────────────────────────────────────────────────
        $w = $users[0];
        Withdrawal::firstOrCreate(
            ['user_id' => $w['user']->id, 'method' => 'mpesa', 'status' => 'pending'],
            [
                'wallet_id'      => $w['live']->id,
                'usd_amount'     => 25.00,
                'local_amount'   => 3250,
                'local_currency' => 'KES',
                'exchange_rate'     => 130,
                'fee_amount'        => 0.00,
                'net_amount'        => 25.00,
                'account_reference' => 'WD-DEMO-' . $w['user']->id,
                'phone'             => $w['user']->phone,
                'requested_at'      => now(),
            ]
        );
        $this->command->info('  ✓ Sample withdrawal created.');

        // ── Sample Notifications ──────────────────────────────────────────────
        $notifData = [
            ['type' => 'deposit_successful',   'title' => 'Deposit Approved',        'message' => 'Your M-Pesa deposit of $10.00 has been credited.'],
            ['type' => 'trade_won',            'title' => 'Trade Won!',               'message' => 'Your EUR/USD buy trade won +$8.50.'],
            ['type' => 'bot_earning',          'title' => 'Bot Earnings Credited',    'message' => 'Your Growth Bot credited +$4.25 (0.85% ROI).'],
            ['type' => 'withdrawal_requested', 'title' => 'Withdrawal Requested',     'message' => 'Your withdrawal of $25.00 is pending review.'],
        ];
        foreach ($users as $i => $u) {
            $notif = $notifData[$i % count($notifData)];
            Notification::create([
                'user_id' => $u['user']->id,
                'type'    => $notif['type'],
                'title'   => $notif['title'],
                'message' => $notif['message'],
            ]);
        }
        $this->command->info('  ✓ Sample notifications created.');

        $this->command->info('Demo seeder complete. All demo passwords: password123');
    }
}
