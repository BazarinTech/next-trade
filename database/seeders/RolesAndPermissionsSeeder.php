<?php

namespace Database\Seeders;

use App\Services\PermissionService;
use App\Services\SettingsService;
use Illuminate\Database\Seeder;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(PermissionService $permissions, SettingsService $settings): void
    {
        $this->command->info('Seeding roles and permissions...');
        $permissions->createDefaultRolesAndPermissions();
        $this->command->info('  ✓ Roles and permissions seeded.');

        $this->command->info('Seeding default system settings...');
        $this->seedSettings($settings);
        $this->command->info('  ✓ System settings seeded.');
    }

    private function seedSettings(SettingsService $settings): void
    {
        $defaults = [
            ['exchange_rate_usd_kes',   130.0,  'number',  'currency',  'USD to KES exchange rate',               true],
            ['usdt_usd_rate',           1.0,    'number',  'currency',  'USDT to USD rate',                       true],
            ['min_deposit_amount',      1.0,    'number',  'payments',  'Minimum deposit amount in USD',          false],
            ['min_withdrawal_amount',   5.0,    'number',  'payments',  'Minimum withdrawal amount in USD',       false],
            ['maintenance_mode',        false,  'boolean', 'platform',  'Put platform into maintenance mode',     false],
            ['allow_registrations',     true,   'boolean', 'platform',  'Allow new user registrations',           false],
            ['deposits_enabled',        true,   'boolean', 'payments',  'Enable/disable all deposits',            false],
            ['mpesa_deposits_enabled',  true,   'boolean', 'payments',  'Enable/disable M-Pesa deposits',         false],
            ['usdt_deposits_enabled',   true,   'boolean', 'payments',  'Enable/disable USDT deposits',           false],
            ['withdrawals_enabled',     true,   'boolean', 'payments',  'Enable/disable all withdrawals',         false],
            ['bot_investments_enabled', true,   'boolean', 'trading',   'Enable/disable bot investments',         false],
            ['live_trading_enabled',    true,   'boolean', 'trading',   'Enable/disable live trading simulation', false],
        ];

        foreach ($defaults as [$key, $value, $type, $group, $description, $isPublic]) {
            // Only seed if key does not exist yet
            if (!\App\Models\SystemSetting::where('key', $key)->exists()) {
                $settings->set($key, $value, $type, $group, $description, $isPublic);
            }
        }
    }
}
