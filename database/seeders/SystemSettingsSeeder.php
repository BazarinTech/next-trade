<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SystemSettingsSeeder extends Seeder
{
    public function run(): void
    {
        $now      = now();
        $settings = [
            // ── General ──────────────────────────────────────────────────────
            [
                'key'         => 'site_name',
                'value'       => 'NextTrade',
                'type'        => 'string',
                'group'       => 'general',
                'description' => 'Platform display name shown in the browser title and emails',
                'is_public'   => true,
            ],
            [
                'key'         => 'site_logo_url',
                'value'       => '',
                'type'        => 'string',
                'group'       => 'general',
                'description' => 'URL of the platform logo (upload an image to replace)',
                'is_public'   => true,
            ],
            [
                'key'         => 'maintenance_mode',
                'value'       => '0',
                'type'        => 'boolean',
                'group'       => 'general',
                'description' => 'Put the platform in maintenance mode (non-admins see a maintenance page)',
                'is_public'   => false,
            ],

            // ── Support ───────────────────────────────────────────────────────
            [
                'key'         => 'support_chat_url',
                'value'       => '',
                'type'        => 'string',
                'group'       => 'support',
                'description' => 'Link opened when a user clicks "Chat with Support" (e.g. WhatsApp or Telegram)',
                'is_public'   => false,
            ],
            [
                'key'         => 'community_url',
                'value'       => '',
                'type'        => 'string',
                'group'       => 'support',
                'description' => 'Link opened when a user clicks "Join Trading Community"',
                'is_public'   => false,
            ],

            // ── Referral ──────────────────────────────────────────────────────
            [
                'key'         => 'referral_enabled',
                'value'       => '1',
                'type'        => 'boolean',
                'group'       => 'referral',
                'description' => 'Enable or disable the referral/invite programme',
                'is_public'   => false,
            ],
            [
                'key'         => 'referral_commission_rate',
                'value'       => '3',
                'type'        => 'number',
                'group'       => 'referral',
                'description' => 'Commission percentage paid to the referrer on each deposit (e.g. 3 = 3%)',
                'is_public'   => false,
            ],
            [
                'key'         => 'referral_min_deposit_usd',
                'value'       => '1',
                'type'        => 'number',
                'group'       => 'referral',
                'description' => 'Minimum deposit amount (USD) required to trigger a referral commission',
                'is_public'   => false,
            ],

            // ── System ────────────────────────────────────────────────────────
            [
                'key'         => 'price_tick_retention_days',
                'value'       => '3',
                'type'        => 'number',
                'group'       => 'system',
                'description' => 'How many days of price tick history to keep (older ticks are pruned by the cleanup job)',
                'is_public'   => false,
            ],
        ];

        foreach ($settings as &$row) {
            $row['created_at'] = $now;
            $row['updated_at'] = $now;
        }

        DB::table('system_settings')->upsert(
            $settings,
            ['key'],
            ['value', 'type', 'group', 'description', 'is_public', 'updated_at']
        );

        $this->command?->info('  ✓ System settings seeded.');
    }
}
