<?php

namespace Database\Seeders;

use App\Services\PermissionService;
use App\Services\SettingsService;
use Illuminate\Database\Seeder;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(PermissionService $permissions, SettingsService $settings): void
    {
        $this->command?->info('Seeding roles and permissions...');
        $permissions->createDefaultRolesAndPermissions();
        $this->command?->info('  ✓ Roles and permissions seeded.');

        $this->command?->info('Seeding default system settings...');
        $this->seedSettings($settings);
        $this->command?->info('  ✓ System settings seeded.');
    }

    private function seedSettings(SettingsService $settings): void
    {
        $defaults = [
            // Currency
            ['exchange_rate_usd_kes',   130.0,  'number',  'currency',  'USD to KES exchange rate',                                    true],
            ['usdt_usd_rate',           1.0,    'number',  'currency',  'USDT to USD rate',                                            true],
            // Payments
            ['min_deposit_amount',      1.0,    'number',  'payments',  'Minimum deposit amount in USD',                               false],
            ['min_withdrawal_amount',   5.0,    'number',  'payments',  'Minimum withdrawal amount in USD',                            false],
            ['deposits_enabled',        true,   'boolean', 'payments',  'Enable/disable all deposits',                                 false],
            ['mpesa_deposits_enabled',  true,   'boolean', 'payments',  'Enable/disable M-Pesa deposits',                              false],
            ['usdt_deposits_enabled',   true,   'boolean', 'payments',  'Enable/disable USDT deposits',                                false],
            ['withdrawals_enabled',     true,   'boolean', 'payments',  'Enable/disable all withdrawals',                              false],
            // Platform
            ['maintenance_mode',        false,  'boolean', 'platform',  'Put platform into maintenance mode',                          false],
            ['allow_registrations',     true,   'boolean', 'platform',  'Allow new user registrations',                                false],
            // Trading
            ['bot_investments_enabled', true,   'boolean', 'trading',   'Enable/disable bot investments',                              false],
            ['live_trading_enabled',    true,   'boolean', 'trading',   'Enable/disable live trading simulation',                      false],
            // Referral
            ['referral_enabled',        true,   'boolean', 'referral',  'Enable or disable the referral/invite programme',             false],
            ['referral_commission_rate', 3.0,   'number',  'referral',  'Commission percentage paid to the referrer on each deposit',  false],
            ['referral_min_deposit_usd', 1.0,   'number',  'referral',  'Minimum deposit amount (USD) required to trigger a referral', false],
            // Branding
            ['site_name',               'NextTrade', 'string', 'branding', 'Displayed in the navbar, auth pages and browser tab.',     true],
            ['site_logo_url',           '',     'string',  'branding',  'Full URL to your logo image. Leave empty to use default.',    true],
            // Support
            ['support_chat_url',        '',     'string',  'support',   'Link for customer support chat (WhatsApp, Telegram, etc.)',   false],
            ['community_url',           '',     'string',  'support',   'Link to the trading community (e.g. Telegram group URL)',     false],
            // System
            ['price_tick_retention_days', 3.0,  'number',  'system',   'Days of price tick history to keep before pruning',           false],
        ];

        foreach ($defaults as [$key, $value, $type, $group, $description, $isPublic]) {
            if (!\App\Models\SystemSetting::where('key', $key)->exists()) {
                $settings->set($key, $value, $type, $group, $description, $isPublic);
            }
        }
    }
}
