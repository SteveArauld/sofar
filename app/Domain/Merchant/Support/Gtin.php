<?php

namespace App\Domain\Merchant\Support;

final class Gtin
{
    public static function isValid(?string $value): bool
    {
        $digits = preg_replace('/\D/', '', (string) $value);

        if ($digits === '' || ! in_array(strlen($digits), [8, 12, 13, 14], true)) {
            return false;
        }

        return self::checksum($digits) === (int) $digits[-1];
    }

    public static function normalize(?string $value): ?string
    {
        $digits = preg_replace('/\D/', '', (string) $value);

        return self::isValid($digits) ? $digits : null;
    }

    /**
     * GS1 check digit: weights 3/1 from the right, excluding the check digit.
     */
    public static function checksum(string $digits): int
    {
        $body = substr($digits, 0, -1);
        $sum = 0;
        $weight = 3;

        for ($i = strlen($body) - 1; $i >= 0; $i--) {
            $sum += (int) $body[$i] * $weight;
            $weight = $weight === 3 ? 1 : 3;
        }

        return (10 - ($sum % 10)) % 10;
    }
}
