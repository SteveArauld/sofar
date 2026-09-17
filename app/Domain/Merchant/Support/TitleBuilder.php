<?php

namespace App\Domain\Merchant\Support;

use Illuminate\Support\Str;

final class TitleBuilder
{
    /** @var list<string> */
    private const PROMO_WORDS = [
        'promo', 'promoção', 'promocao', 'oferta', 'melhor preço', 'melhor preco',
        'saldo', 'saldos', 'liquidação', 'liquidacao', 'desconto', 'grátis', 'gratis',
        'sale', '!!!',
    ];

    public static function build(string $name): string
    {
        $title = self::clean($name);

        if (self::looksAllCaps($title)) {
            $title = self::titleCase($title);
        }

        if (self::hasPromo($title)) {
            $title = self::stripPromo($title);
        }

        return Str::limit($title, 150, '');
    }

    public static function clean(?string $value): string
    {
        $value = html_entity_decode((string) $value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $value = preg_replace('/\s+/u', ' ', $value) ?? '';

        return trim($value);
    }

    private static function looksAllCaps(string $s): bool
    {
        if (! str_contains($s, ' ')) {
            return false;
        }

        $letters = preg_replace('/[^\p{L}]/u', '', $s) ?? '';

        return $letters !== '' && mb_strtoupper($letters, 'UTF-8') === $letters;
    }

    private static function titleCase(string $s): string
    {
        $s = mb_convert_case($s, MB_CASE_TITLE, 'UTF-8');

        return preg_replace_callback('/\b(De|Do|Da|Dos|Das|E|Em|Com|Para|A|O|As|Os|Ao|Aos)\b/u',
            fn (array $m): string => mb_strtolower($m[1], 'UTF-8'), $s) ?? $s;
    }

    private static function hasPromo(string $title): bool
    {
        $hay = Str::lower($title);

        foreach (self::PROMO_WORDS as $word) {
            if (str_contains($hay, $word) || str_contains($title, '!!!') || str_contains($title, '🔥') || str_contains($title, '⭐')) {
                return true;
            }
        }

        return false;
    }

    private static function stripPromo(string $s): string
    {
        $s = preg_replace('/[\x{1F000}-\x{1FAFF}\x{2600}-\x{27BF}]/u', '', $s) ?? $s;
        $s = preg_replace('/!{2,}/', '', $s) ?? $s;

        foreach (self::PROMO_WORDS as $word) {
            $s = preg_replace('/\b'.preg_quote($word, '/').'\b/iu', '', $s) ?? $s;
        }

        return trim(preg_replace('/\s{2,}/', ' ', $s) ?? $s, ' -–—|');
    }
}
