<?php

namespace App\DTO\Merchant;

use App\Domain\Merchant\MerchantPolicy;
use App\Domain\Merchant\Support\Availability;
use App\Domain\Merchant\Support\PriceFormatter;

final class GoogleProductData
{
    /**
     * @param  list<string>  $extraImages
     */
    public function __construct(
        public readonly string $id,
        public readonly string $title,
        public readonly string $description,
        public readonly string $link,
        public readonly string $imageLink,
        public readonly array $extraImages,
        public readonly Availability $availability,
        public readonly string $condition,
        public readonly float $priceValue,
        public readonly ?float $salePriceValue,
        public readonly string $currency,
        public readonly string $brand,
        public readonly ?string $gtin,
        public readonly ?string $mpn,
        public readonly bool $identifierExists,
        public readonly int $googleProductCategory,
        public readonly string $productType,
        public readonly ?string $itemGroupId,
        public readonly ?string $color,
        public readonly ?string $size,
        public readonly int $qualityId = 0,
        public readonly int $qualityImg = 0,
        public readonly int $qualityDesc = 0,
        public readonly string $risk = 'OK',
        public readonly string $corrections = '',
    ) {}

    public function formattedPrice(): string
    {
        return PriceFormatter::format($this->priceValue, $this->currency);
    }

    public function offerPriceValue(): float
    {
        return $this->salePriceValue ?? $this->priceValue;
    }

    public function formattedSalePrice(): ?string
    {
        return $this->salePriceValue !== null
            ? PriceFormatter::format($this->salePriceValue, $this->currency)
            : null;
    }

    /**
     * JSON-LD Product + Offer — same numbers as the Merchant feed.
     *
     * @return array<string, mixed>
     */
    public function toJsonLd(): array
    {
        $offerPrice = $this->offerPriceValue();

        $product = [
            '@context' => 'https://schema.org',
            '@type' => 'Product',
            'name' => $this->title,
            'image' => array_values(array_filter([$this->imageLink, ...$this->extraImages])),
            'description' => $this->description,
            'sku' => $this->mpn ?: $this->id,
            'brand' => [
                '@type' => 'Brand',
                'name' => $this->brand,
            ],
            'offers' => [
                '@type' => 'Offer',
                'url' => $this->link,
                'price' => PriceFormatter::amount($offerPrice),
                'priceCurrency' => $this->currency,
                'availability' => $this->availability->schemaUrl(),
                'itemCondition' => 'https://schema.org/NewCondition',
                'hasMerchantReturnPolicy' => MerchantPolicy::returnPolicySchema(),
                'shippingDetails' => MerchantPolicy::shippingDetailsSchema(),
            ],
        ];

        if ($this->gtin !== null) {
            $len = strlen($this->gtin);
            $key = match ($len) {
                8 => 'gtin8',
                12 => 'gtin12',
                13 => 'gtin13',
                14 => 'gtin14',
                default => 'gtin',
            };
            $product[$key] = $this->gtin;
        }

        if ($this->mpn !== null) {
            $product['mpn'] = $this->mpn;
        }

        return $product;
    }
}
