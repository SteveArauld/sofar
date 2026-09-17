<?php

namespace App\Domain\Merchant\Support;

final class PriceFormatter
{
    public static function format(float $amount, ?string $currency = null): string
    {
        $currency ??= (string) config('feed.currency', 'EUR');

        return number_format($amount, 2, '.', '').' '.strtoupper($currency);
    }

    public static function amount(float $amount): string
    {
        return number_format($amount, 2, '.', '');
    }
}
