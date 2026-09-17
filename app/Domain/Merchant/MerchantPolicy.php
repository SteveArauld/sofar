<?php

namespace App\Domain\Merchant;

use App\Domain\Merchant\Support\PriceFormatter;

final class MerchantPolicy
{
    public static function deliveryCopy(): string
    {
        return 'Entrega em 1 a 3 dias úteis (preparação: 1 dia útil). Envio gratuito para todo o Portugal.';
    }

    public static function shippingLabel(): string
    {
        return 'Envio gratuito para todo o Portugal';
    }

    public static function returnDays(): int
    {
        return (int) config('feed.return_days', 14);
    }

    public static function handlingMin(): int
    {
        return (int) config('feed.handling_time.min', 1);
    }

    public static function handlingMax(): int
    {
        return (int) config('feed.handling_time.max', 1);
    }

    public static function transitMin(): int
    {
        return (int) config('feed.transit_time.min', 0);
    }

    public static function transitMax(): int
    {
        return (int) config('feed.transit_time.max', 2);
    }

    public static function shippingPrice(): float
    {
        return (float) config('feed.shipping_price', 0);
    }

    public static function currency(): string
    {
        return strtoupper((string) config('feed.currency', 'EUR'));
    }

    public static function targetCountry(): string
    {
        return strtoupper((string) config('feed.target_country', 'PT'));
    }

    public static function brandFallback(): string
    {
        return (string) config('feed.store_brand', 'DFP Interiores');
    }

    public static function baseUrl(): string
    {
        return rtrim((string) config('feed.base_url', 'https://dfpinteriores.com'), '/');
    }

    /**
     * @return array<string, mixed>
     */
    public static function organizationSchema(): array
    {
        $base = self::baseUrl();

        return [
            '@context' => 'https://schema.org',
            '@graph' => [
                [
                    '@type' => ['Organization', 'OnlineStore'],
                    '@id' => $base.'/#organization',
                    'name' => 'DFP Interiores',
                    'legalName' => 'DFP Interiores, Unipessoal Lda',
                    'url' => $base,
                    'email' => 'contacto@dfpinteriores.com',
                    'telephone' => '+351912026453',
                    'vatID' => 'PT504074571',
                    'address' => [
                        '@type' => 'PostalAddress',
                        'streetAddress' => 'Rua José Francisco Fragoso, n.º 45',
                        'postalCode' => '7080-035',
                        'addressLocality' => 'Vendas Novas',
                        'addressCountry' => 'PT',
                    ],
                    'contactPoint' => [
                        '@type' => 'ContactPoint',
                        'contactType' => 'customer service',
                        'telephone' => '+351912026453',
                        'email' => 'contacto@dfpinteriores.com',
                        'areaServed' => 'PT',
                        'availableLanguage' => 'Portuguese',
                    ],
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function returnPolicySchema(): array
    {
        return [
            '@type' => 'MerchantReturnPolicy',
            'applicableCountry' => self::targetCountry(),
            'returnPolicyCategory' => 'https://schema.org/MerchantReturnFiniteReturnWindow',
            'merchantReturnDays' => self::returnDays(),
            'returnMethod' => 'https://schema.org/ReturnByMail',
            'returnFees' => 'https://schema.org/ReturnShippingFees',
            'returnPolicyCountry' => [
                '@type' => 'Country',
                'name' => self::targetCountry(),
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function shippingDetailsSchema(): array
    {
        return [
            '@type' => 'OfferShippingDetails',
            'shippingRate' => [
                '@type' => 'MonetaryAmount',
                'value' => PriceFormatter::amount(self::shippingPrice()),
                'currency' => self::currency(),
            ],
            'shippingDestination' => [
                '@type' => 'DefinedRegion',
                'addressCountry' => self::targetCountry(),
            ],
            'deliveryTime' => [
                '@type' => 'ShippingDeliveryTime',
                'handlingTime' => [
                    '@type' => 'QuantitativeValue',
                    'minValue' => self::handlingMin(),
                    'maxValue' => self::handlingMax(),
                    'unitCode' => 'DAY',
                ],
                'transitTime' => [
                    '@type' => 'QuantitativeValue',
                    'minValue' => self::transitMin(),
                    'maxValue' => self::transitMax(),
                    'unitCode' => 'DAY',
                ],
            ],
        ];
    }
}
