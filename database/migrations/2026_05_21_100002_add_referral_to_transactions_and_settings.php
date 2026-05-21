<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Extend transactions.type enum to include referral_commission
        DB::statement("ALTER TABLE transactions MODIFY COLUMN type ENUM(
            'deposit',
            'withdrawal',
            'trade_profit',
            'trade_loss',
            'bot_profit',
            'bot_investment',
            'adjustment',
            'referral_commission'
        )");

        // Seed referral system settings
        $now = now();
        DB::table('system_settings')->upsert([
            [
                'key'         => 'referral_enabled',
                'value'       => '1',
                'type'        => 'boolean',
                'group'       => 'referral',
                'description' => 'Enable or disable the referral/invite programme',
                'is_public'   => false,
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
            [
                'key'         => 'referral_commission_rate',
                'value'       => '3',
                'type'        => 'number',
                'group'       => 'referral',
                'description' => 'Commission percentage paid to the referrer on each deposit (e.g. 3 = 3%)',
                'is_public'   => false,
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
            [
                'key'         => 'referral_min_deposit_usd',
                'value'       => '1',
                'type'        => 'number',
                'group'       => 'referral',
                'description' => 'Minimum deposit amount (USD) required to trigger a referral commission',
                'is_public'   => false,
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
        ], ['key'], ['value', 'type', 'group', 'description', 'updated_at']);
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE transactions MODIFY COLUMN type ENUM(
            'deposit',
            'withdrawal',
            'trade_profit',
            'trade_loss',
            'bot_profit',
            'bot_investment',
            'adjustment'
        )");

        DB::table('system_settings')->whereIn('key', [
            'referral_enabled',
            'referral_commission_rate',
            'referral_min_deposit_usd',
        ])->delete();
    }
};
