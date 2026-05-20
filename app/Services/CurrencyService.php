<?php

namespace App\Services;

class CurrencyService
{
    public function __construct(private SettingsService $settings) {}

    public function getUsdKesRate(): float
    {
        $rate = $this->settings->number('exchange_rate_usd_kes', 0);
        return $rate > 0 ? $rate : (float) env('USD_KES_RATE', 130);
    }

    public function kesToUsd(float $amount): float
    {
        $rate = $this->getUsdKesRate();
        if ($rate <= 0) {
            return 0.0;
        }
        return round($amount / $rate, 2);
    }

    public function usdToKes(float $amount): float
    {
        return round($amount * $this->getUsdKesRate(), 2);
    }

    public function getUsdtUsdRate(): float
    {
        $rate = $this->settings->number('usdt_usd_rate', 0);
        return $rate > 0 ? $rate : (float) env('USDT_USD_RATE', 1);
    }
}
