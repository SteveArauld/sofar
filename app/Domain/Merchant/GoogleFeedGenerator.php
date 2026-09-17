<?php

namespace App\Domain\Merchant;

use App\Domain\Merchant\Support\PriceFormatter;
use App\DTO\Merchant\GoogleProductData;

final class GoogleFeedGenerator
{
    /**
     * @param  iterable<GoogleProductData>  $items
     */
    public function xml(iterable $items): string
    {
        $out = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
        $out .= '<rss version="2.0" xmlns:g="http://base.google.com/ns/1.0">'."\n  <channel>\n";
        $out .= '    <title>'.$this->esc(config('app.name')).' — Catálogo</title>'."\n";
        $out .= '    <link>'.$this->esc(MerchantPolicy::baseUrl()).'</link>'."\n";
        $out .= '    <description>Feed de produtos para Google Merchant Center (Portugal)</description>'."\n";
        $out .= '    <lastBuildDate>'.now()->toRfc2822String().'</lastBuildDate>'."\n";

        foreach ($items as $item) {
            $out .= $this->item($item);
        }

        $out .= "  </channel>\n</rss>\n";

        return $out;
    }

    /**
     * @param  iterable<GoogleProductData>  $items
     */
    public function write(iterable $items, string $path): void
    {
        $dir = dirname($path);
        if (! is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        file_put_contents($path, $this->xml($items));
    }

    private function item(GoogleProductData $item): string
    {
        $x = "    <item>\n";
        $x .= $this->t('g:id', $item->id);
        $x .= $this->c('title', $item->title);
        $x .= $this->c('description', $item->description);
        $x .= $this->t('link', $item->link);
        $x .= $this->t('g:image_link', $item->imageLink);
        foreach ($item->extraImages as $extra) {
            $x .= $this->t('g:additional_image_link', $extra);
        }
        $x .= $this->t('g:availability', $item->availability->value);
        $x .= $this->t('g:condition', $item->condition);
        $x .= $this->t('g:price', $item->formattedPrice());
        if ($item->formattedSalePrice() !== null) {
            $x .= $this->t('g:sale_price', $item->formattedSalePrice());
        }
        $x .= $this->c('g:brand', $item->brand);
        if ($item->gtin) {
            $x .= $this->t('g:gtin', $item->gtin);
        }
        if ($item->mpn) {
            $x .= $this->c('g:mpn', $item->mpn);
        }
        $x .= $this->t('g:identifier_exists', $item->identifierExists ? 'yes' : 'no');
        $x .= $this->t('g:google_product_category', (string) $item->googleProductCategory);
        if ($item->productType !== '') {
            $x .= $this->c('g:product_type', $item->productType);
        }
        if ($item->itemGroupId) {
            $x .= $this->t('g:item_group_id', $item->itemGroupId);
        }
        if ($item->color) {
            $x .= $this->c('g:color', $item->color);
        }
        if ($item->size) {
            $x .= $this->c('g:size', $item->size);
        }
        $x .= "      <g:shipping>\n";
        $x .= '        <g:country>'.$this->esc(MerchantPolicy::targetCountry()).'</g:country>'."\n";
        $x .= '        <g:price>'.$this->esc(PriceFormatter::format(MerchantPolicy::shippingPrice(), MerchantPolicy::currency())).'</g:price>'."\n";
        $x .= "      </g:shipping>\n";
        $x .= $this->t('g:min_handling_time', (string) MerchantPolicy::handlingMin());
        $x .= $this->t('g:max_handling_time', (string) MerchantPolicy::handlingMax());
        $x .= $this->t('g:min_transit_time', (string) MerchantPolicy::transitMin());
        $x .= $this->t('g:max_transit_time', (string) MerchantPolicy::transitMax());
        $x .= "    </item>\n";

        return $x;
    }

    private function t(string $name, string $value): string
    {
        return '      <'.$name.'>'.$this->esc($value).'</'.$name.">\n";
    }

    private function c(string $name, string $value): string
    {
        $value = str_replace(']]>', ']]]]><![CDATA[>', $value);

        return '      <'.$name.'><![CDATA['.$value.']]></'.$name.">\n";
    }

    private function esc(string $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES | ENT_XML1, 'UTF-8');
    }
}
