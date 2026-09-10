<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Flux produits Google Merchant Center (RSS 2.0 + espace de noms g:).
 * Doc : https://support.google.com/merchants/answer/7052112
 *
 *  - GET /feed/produtos.xml            -> affichage dans le navigateur
 *  - GET /feed/produtos.xml/download   -> téléchargement du fichier
 */
class FeedController extends Controller
{
    /** Affiche le flux XML inline dans le navigateur. */
    public function xml(Request $request): StreamedResponse
    {
        return $this->stream(download: false);
    }

    /** Force le téléchargement du flux XML. */
    public function download(Request $request): StreamedResponse
    {
        return $this->stream(download: true);
    }

    private function stream(bool $download): StreamedResponse
    {
        $headers = [
            'Content-Type'  => 'application/xml; charset=UTF-8',
            'Cache-Control' => 'public, max-age=1800',
        ];

        if ($download) {
            $headers['Content-Disposition'] = 'attachment; filename="google-merchant-'.now()->format('Y-m-d').'.xml"';
        }

        return response()->stream(function () {
            $out = fopen('php://output', 'w');

            fwrite($out, '<?xml version="1.0" encoding="UTF-8"?>'."\n");
            fwrite($out, '<rss version="2.0" xmlns:g="http://base.google.com/ns/1.0">'."\n");
            fwrite($out, "  <channel>\n");
            fwrite($out, '    <title>'.$this->esc(config('app.name', 'Loja')).' — Catálogo</title>'."\n");
            fwrite($out, '    <link>'.$this->esc(url('/')).'</link>'."\n");
            fwrite($out, '    <description>'.$this->esc('Feed de produtos para Google Merchant Center').'</description>'."\n");
            fwrite($out, '    <lastBuildDate>'.now()->toRfc2822String().'</lastBuildDate>'."\n");

            Product::query()
                ->with('images')
                ->where('price', '>', 0)
                ->whereHas('images')
                ->orderBy('id')
                ->chunk(400, function ($products) use ($out) {
                    foreach ($products as $product) {
                        fwrite($out, $this->item($product));
                    }
                    flush();
                });

            fwrite($out, "  </channel>\n");
            fwrite($out, "</rss>\n");
            fclose($out);
        }, 200, $headers);
    }

    private function item(Product $p): string
    {
        $currency = strtoupper($p->currency ?: 'EUR');
        $price = (float) $p->price;
        $priceBefore = $p->price_before !== null ? (float) $p->price_before : null;

        $hasSale = $priceBefore !== null && $priceBefore > $price;
        $regularPrice = $hasSale ? $priceBefore : $price;
        $salePrice = $hasSale ? $price : null;

        $link = url('/'.$p->slug);

        $images = $p->images->map(fn ($i) => url($i->path))->filter()->values();
        $imageLink = $images->first();
        if (! $imageLink) {
            return ''; // GMC exige une image
        }

        $title = Str::limit($this->clean($p->name), 145, '');

        $brand = $p->brand ?: config('app.name', '');
        // filet de sécurité : ancienne marque scrappée
        if (Str::contains($brand, 'Feira dos Sof', true)) {
            $brand = config('app.name', 'DFP Interiores');
        }

        $description = $this->clean(strip_tags(
            $p->short_description_html ?: $p->long_description_html ?: ''
        ));
        if ($description === '') {
            $description = trim($this->clean($p->name).' — '.$this->clean((string) $p->category_name));
            $description = rtrim($description, ' —');
            $description .= '. Disponível na '.config('app.name', 'loja').'.';
        }
        $description = Str::limit($description, 4900, '');

        // La source vend « por encomenda » : hors stock => backorder (commandable),
        // jamais out_of_stock, sauf article réellement à stock uniquement.
        $stockOnly = data_get($p->source_payload, 'vende_apenas_stock') === 'Y';
        $availability = $p->in_stock
            ? 'in_stock'
            : ($stockOnly ? 'out_of_stock' : 'backorder');

        $condition = 'new';

        $gtin = null;
        $ean = preg_replace('/\D/', '', (string) $p->ean);
        if ($ean !== '' && in_array(strlen($ean), [8, 12, 13, 14], true)) {
            $gtin = $ean;
        }
        $mpn = $p->sku ?: null;

        $x = "    <item>\n";
        $x .= $this->tag('g:id', (string) $p->id);
        $x .= $this->cdata('title', $title);
        $x .= $this->cdata('description', $description);
        $x .= $this->tag('link', $link);
        $x .= $this->tag('g:image_link', $imageLink);

        foreach ($images->slice(1, 10) as $extra) {
            $x .= $this->tag('g:additional_image_link', $extra);
        }

        $x .= $this->tag('g:availability', $availability);
        $x .= $this->tag('g:condition', $condition);
        $x .= $this->tag('g:price', $this->money($regularPrice, $currency));
        if ($salePrice !== null) {
            $x .= $this->tag('g:sale_price', $this->money($salePrice, $currency));
        }

        $x .= $this->cdata('g:brand', $brand);
        $x .= $this->tag('g:google_product_category', $this->googleCategory($p));

        $productType = $this->productType($p);
        if ($productType !== '') {
            $x .= $this->cdata('g:product_type', $productType);
        }

        if ($gtin) {
            $x .= $this->tag('g:gtin', $gtin);
        }
        if ($mpn) {
            $x .= $this->cdata('g:mpn', (string) $mpn);
        }
        // Signale explicitement à Google si un identifiant unique (GTIN, ou MPN + marque) existe
        $x .= $this->tag('g:identifier_exists', ($gtin || ($mpn && $brand !== '')) ? 'yes' : 'no');
        $x .= "    </item>\n";

        return $x;
    }

    private function money(float $value, string $currency): string
    {
        return number_format($value, 2, '.', '').' '.$currency;
    }

    private function tag(string $name, string $value): string
    {
        return '      <'.$name.'>'.$this->esc($value).'</'.$name.">\n";
    }

    private function cdata(string $name, string $value): string
    {
        $value = str_replace(']]>', ']]]]><![CDATA[>', $value);

        return '      <'.$name.'><![CDATA['.$value.']]></'.$name.">\n";
    }

    private function esc(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_XML1, 'UTF-8');
    }

    /** Nettoie les espaces / entités / retours ligne d'un texte. */
    private function clean(?string $value): string
    {
        $value = html_entity_decode((string) $value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $value = preg_replace('/\s+/u', ' ', $value);

        return trim((string) $value);
    }

    /** Catégorie Google Product Taxonomy (chaîne texte, acceptée par GMC). */
    private function googleCategory(Product $p): string
    {
        $hay = Str::lower(($p->category_name ?? '').' '.($p->category_slug ?? '').' '.$p->name);

        return match (true) {
            Str::contains($hay, ['sofa', 'sofá', 'chaise', 'cadeirao', 'cadeirão', 'poltrona']) => 'Home & Garden > Furniture > Sofas',
            Str::contains($hay, ['colchao', 'colchão', 'colchoes', 'colchões', 'mattress'])      => 'Home & Garden > Furniture > Beds & Accessories > Mattresses',
            Str::contains($hay, ['mesa de cabeceira', 'mesas de cabeceira', 'mesita'])           => 'Home & Garden > Furniture > Beds & Accessories > Nightstands',
            Str::contains($hay, ['cama', 'estrado', 'sommier', 'sommiers', 'cabeceira'])         => 'Home & Garden > Furniture > Beds & Accessories > Bed Frames',
            Str::contains($hay, ['roupeiro', 'armario', 'armário', 'wardrobe'])                  => 'Home & Garden > Furniture > Armoires & Wardrobes',
            Str::contains($hay, ['mesa', 'table', 'aparador'])                                   => 'Home & Garden > Furniture > Tables',
            Str::contains($hay, ['cadeira', 'chair', 'banco'])                                   => 'Home & Garden > Furniture > Chairs',
            Str::contains($hay, ['espelho', 'mirror'])                                           => 'Home & Garden > Decor > Mirrors',
            Str::contains($hay, ['candeeiro', 'lamp', 'iluminacao', 'iluminação'])               => 'Home & Garden > Lighting',
            Str::contains($hay, ['tapete', 'carpete', 'rug'])                                    => 'Home & Garden > Decor > Rugs',
            default                                                                             => 'Home & Garden > Furniture',
        };
    }

    /** Fil d'Ariane -> g:product_type. */
    private function productType(Product $p): string
    {
        $crumbs = collect($p->breadcrumb ?: [])
            ->map(fn ($c) => $this->clean((string) ($c['nome'] ?? $c['name'] ?? '')))
            ->filter()
            ->values();

        if ($crumbs->isNotEmpty()) {
            return $crumbs->implode(' > ');
        }

        return $this->clean((string) $p->category_name);
    }
}
