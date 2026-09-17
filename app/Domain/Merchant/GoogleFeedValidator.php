<?php

namespace App\Domain\Merchant;

use App\Domain\Merchant\Support\Gtin;
use App\DTO\Merchant\GoogleProductData;
use Illuminate\Support\Str;

final class GoogleFeedValidator
{
    /**
     * @return list<string>
     */
    public function validate(GoogleProductData $item): array
    {
        $errors = [];

        if ($item->id === '' || strlen($item->id) > 50) {
            $errors[] = 'id inválido ou > 50 caracteres';
        }
        if ($item->title === '' || mb_strlen($item->title) > 150) {
            $errors[] = 'title vazio ou > 150';
        }
        if (preg_match('/\b(promo|desconto|sale|!!!)\b/iu', $item->title)) {
            $errors[] = 'title contém promo';
        }
        if ($item->description === '' || mb_strlen($item->description) > 5000) {
            $errors[] = 'description vazia ou > 5000';
        }
        if (! Str::startsWith($item->link, 'https://')) {
            $errors[] = 'link não é HTTPS';
        }
        if (! Str::startsWith($item->imageLink, 'https://')) {
            $errors[] = 'image_link não é HTTPS';
        }
        if (! preg_match('/^\d+\.\d{2} [A-Z]{3}$/', $item->formattedPrice())) {
            $errors[] = 'price formato inválido';
        }
        if ($item->offerPriceValue() <= 0) {
            $errors[] = 'preço de oferta <= 0';
        }
        if ($item->gtin !== null && ! Gtin::isValid($item->gtin)) {
            $errors[] = 'GTIN checksum inválido';
        }
        if (! $item->identifierExists && ($item->gtin || $item->mpn)) {
            $errors[] = 'identifier_exists=no com gtin/mpn presentes';
        }
        if ($item->brand === '' || Str::contains(Str::lower($item->brand), ['generic', 'n/a', 'feira dos sof'])) {
            $errors[] = 'brand genérica ou inválida';
        }

        return $errors;
    }
}
