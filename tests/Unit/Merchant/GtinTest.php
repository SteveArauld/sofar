<?php

namespace Tests\Unit\Merchant;

use App\Domain\Merchant\Support\Gtin;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class GtinTest extends TestCase
{
    public function test_rejects_empty_and_short_values(): void
    {
        $this->assertFalse(Gtin::isValid(null));
        $this->assertFalse(Gtin::isValid(''));
        $this->assertFalse(Gtin::isValid('123'));
        $this->assertFalse(Gtin::isValid('ABC'));
    }

    public function test_accepts_checksum_valid_gtin13(): void
    {
        $body = '560123456789';
        $gtin = $body.Gtin::checksum($body.'0');

        $this->assertTrue(Gtin::isValid($gtin));
        $this->assertSame($gtin, Gtin::normalize($gtin));
    }

    public function test_rejects_invalid_checksum(): void
    {
        $body = '560123456789';
        $valid = $body.Gtin::checksum($body.'0');
        $invalid = substr($valid, 0, -1).((int) $valid[-1] === 0 ? '1' : '0');

        $this->assertFalse(Gtin::isValid($invalid));
        $this->assertNull(Gtin::normalize($invalid));
    }

    #[DataProvider('lengths')]
    public function test_accepts_supported_lengths(int $bodyLen): void
    {
        $body = str_repeat('1', $bodyLen);
        $gtin = $body.Gtin::checksum($body.'0');

        $this->assertTrue(Gtin::isValid($gtin));
    }

    /**
     * @return array<string, array{0: int}>
     */
    public static function lengths(): array
    {
        return [
            'gtin8' => [7],
            'gtin12' => [11],
            'gtin13' => [12],
            'gtin14' => [13],
        ];
    }
}
