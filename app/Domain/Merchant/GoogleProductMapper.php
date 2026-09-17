<?php

namespace App\Domain\Merchant;

use App\Domain\Merchant\Support\Availability;
use App\Domain\Merchant\Support\Gtin;
use App\Domain\Merchant\Support\PriceFormatter;
use App\Domain\Merchant\Support\TitleBuilder;
use App\DTO\Merchant\GoogleProductData;
use App\Models\Product;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

final class GoogleProductMapper
{
    /** @var array<string, array{id: int, path: string}> */
    private const GCATEGORY = [
        'sofas' => ['id' => 635, 'path' => 'Furniture > Sofas'],
        'mattress' => ['id' => 2696, 'path' => 'Furniture > Beds & Accessories > Mattresses'],
        'nightstand' => ['id' => 6349, 'path' => 'Furniture > Beds & Accessories > Nightstands'],
        'bedframe' => ['id' => 505764, 'path' => 'Furniture > Beds & Accessories > Bed Frames'],
        'wardrobe' => ['id' => 6350, 'path' => 'Furniture > Armoires & Wardrobes'],
        'table' => ['id' => 6362, 'path' => 'Furniture > Tables'],
        'chair' => ['id' => 443, 'path' => 'Furniture > Chairs'],
        'mirror' => ['id' => 6335, 'path' => 'Home & Garden > Decor > Mirrors'],
        'rug' => ['id' => 570, 'path' => 'Home & Garden > Decor > Rugs'],
        'lighting' => ['id' => 594, 'path' => 'Home & Garden > Lighting'],
        'furniture' => ['id' => 436, 'path' => 'Furniture'],
    ];

    /** @var list<string> */
    private const RESTRICTED = [
        'arma de fogo', 'armas de fogo', 'pistola de bala', 'pistola c\/balas', 'espingarda',
        'munição', 'munições', 'airsoft', 'r[eé]plica de arma',
        'whisky', 'vodka', 'gin ', 'rum ', 'tequila', 'aguardente',
        'tabaco', 'cigarro', 'cigarros', 'vape', 'nicotina',
        'suplemento alimentar', 'emagrecimento', 'medicamento', 'medicamentos',
        'f[aá]rmaco', 'viagra', 'desbloqueio de telem[oó]vel', 'unlock tool', 'iptv',
    ];

    /**
     * Map a catalog product for the landing page (JSON-LD / DOM), even if it is excluded from the feed.
     */
    public function mapForLanding(Product $product): GoogleProductData
    {
        return $this->hydrate($product);
    }

    /**
     * Map a buyable SKU for the Merchant feed. Returns null when the product must be excluded.
     *
     * @param  array<string, int>  $seen
     * @return array{0: array<string, mixed>, 1: ?GoogleProductData}
     */
    public function evaluateForFeed(Product $product, array &$seen = []): array
    {
        $row = [
            'id' => $product->id,
            'title' => (string) $product->name,
            'price' => $product->price,
            'status' => 'EXCLU',
            'reason' => '',
            'risk' => 'OK',
            'corrections' => '',
            'rank' => '',
        ];

        $exclusion = $this->exclusionReason($product);
        if ($exclusion !== null) {
            $row['reason'] = $exclusion;
            if (str_contains($exclusion, 'restringida')) {
                $row['risk'] = 'RISCO ELEVADO';
            }

            return [$row, null];
        }

        $item = $this->hydrate($product);

        if (mb_strlen($item->title) < 3) {
            $row['reason'] = 'titre inexploitable';
            $row['risk'] = 'RISCO ELEVADO';

            return [$row, null];
        }

        $hash = md5(Str::lower($item->title).'|'.basename($item->imageLink).'|'.PriceFormatter::amount($item->offerPriceValue()));
        if (isset($seen[$hash])) {
            $row['reason'] = 'doublon (titre + image + prix)';
            $row['risk'] = $item->risk;
            $row['corrections'] = $item->corrections;

            return [$row, null];
        }
        $seen[$hash] = (int) $product->id;

        $row['status'] = 'INCLUS';
        $row['risk'] = $item->risk;
        $row['corrections'] = $item->corrections;
        $row['title'] = $item->title;

        return [$row, $item];
    }

    public function exclusionReason(Product $product): ?string
    {
        $price = (float) $product->price;
        $min = (float) config('feed.min_price', 80);
        $images = $product->relationLoaded('images')
            ? $product->images
            : $product->images()->get();
        $paths = $images->pluck('path')->filter()->values();

        $excl = [];
        if ($price <= 0) {
            $excl[] = 'prix nul / absent';
        }
        if ($price > 0 && $price < $min) {
            $excl[] = "prix < {$min} €";
        }
        if ($paths->isEmpty()) {
            $excl[] = 'aucune image';
        }
        if (! $product->in_stock) {
            $excl[] = 'hors stock';
        }
        if ($this->isPlaceholder($paths->first())) {
            $excl[] = 'image placeholder';
        }
        if ($this->matchesRestricted(Str::lower($product->name.' '.strip_tags((string) $product->short_description_html)))) {
            $excl[] = 'catégorie restreinte Google';
        }
        $first = $paths->first();
        if ($first && ! is_file(public_path($first))) {
            $excl[] = 'fichier image introuvable';
        }

        return $excl === [] ? null : implode(' ; ', $excl);
    }

    private function hydrate(Product $product): GoogleProductData
    {
        $corr = [];
        $risk = 'OK';

        $rawTitle = TitleBuilder::clean($product->name);
        $title = TitleBuilder::build($product->name);
        if ($title !== $rawTitle && TitleBuilder::build($rawTitle) === $title) {
            if ($this->looksCorrectedCasing($rawTitle, $title)) {
                $corr[] = 'titre normalisé (casse)';
                $risk = 'MÉDIO';
            }
        }
        if ($title !== TitleBuilder::clean($product->name) && str_contains(Str::lower($product->name), 'promo')) {
            $corr[] = 'titre nettoyé (promo/emoji)';
            $risk = 'MÉDIO';
        }

        [$description, $descCorr] = $this->buildDescription($product);
        if ($descCorr) {
            $corr[] = $descCorr;
            $risk = 'MÉDIO';
        }

        $brand = $this->brand($product, $corr, $risk);

        $price = (float) $product->price;
        $priceBefore = $product->price_before !== null ? (float) $product->price_before : null;
        $hasSale = $priceBefore !== null && $priceBefore > $price;

        $images = $product->relationLoaded('images') ? $product->images : $product->images()->get();
        $imgUrls = $images->pluck('path')->filter()->map(fn ($path) => $this->assetUrl((string) $path))->values();

        $gtin = Gtin::normalize($product->ean);
        $mpn = $product->sku ? (string) $product->sku : null;
        $identifierExists = $gtin !== null || ($mpn !== null && $brand !== '');

        $gc = $this->googleCategory($product);
        $color = $this->singleAttribute($product, ['COR', 'color', 'cor']);
        $size = $this->singleAttribute($product, ['MEDIDA', 'TAMANHO', 'size', 'tamanho']);

        return new GoogleProductData(
            id: (string) $product->id,
            title: $title,
            description: Str::limit($description, 5000, ''),
            link: MerchantPolicy::baseUrl().'/'.ltrim((string) $product->slug, '/'),
            imageLink: (string) $imgUrls->first(),
            extraImages: $imgUrls->slice(1, 10)->values()->all(),
            availability: $this->availability($product),
            condition: 'new',
            priceValue: $hasSale ? $priceBefore : $price,
            salePriceValue: $hasSale ? $price : null,
            currency: strtoupper((string) ($product->currency ?: MerchantPolicy::currency())),
            brand: $brand,
            gtin: $gtin,
            mpn: $mpn,
            identifierExists: $identifierExists,
            googleProductCategory: $gc['id'],
            productType: $this->productType($product),
            itemGroupId: null,
            color: $color,
            size: $size,
            qualityId: $gtin ? 2 : ($mpn ? 1 : 0),
            qualityImg: min($imgUrls->count(), 6),
            qualityDesc: min((int) floor(mb_strlen($description) / 200), 10),
            risk: $risk,
            corrections: implode(', ', $corr),
        );
    }

    public function availability(Product $product): Availability
    {
        if ($product->in_stock) {
            return Availability::InStock;
        }

        $stockOnly = data_get($product->source_payload, 'vende_apenas_stock') === 'Y';

        return $stockOnly ? Availability::OutOfStock : Availability::Backorder;
    }

    /**
     * @param  list<string>  $corr
     */
    private function brand(Product $product, array &$corr, string &$risk): string
    {
        $store = MerchantPolicy::brandFallback();
        $current = trim((string) $product->brand) ?: $store;

        if (Str::contains(Str::lower($current), 'feira dos sof') || $current === '') {
            $corr[] = 'marque corrigée';
            $risk = 'MÉDIO';

            return $store;
        }

        return $current;
    }

    /**
     * @return array{0: string, 1: ?string}
     */
    private function buildDescription(Product $product): array
    {
        $raw = $product->long_description_html ?: $product->short_description_html ?: '';
        $hadHtml = $raw !== strip_tags((string) $raw);
        $txt = TitleBuilder::clean(strip_tags((string) $raw));

        $before = $txt;
        $txt = preg_replace('#https?://\S+#i', '', $txt) ?? $txt;
        $txt = preg_replace('/[\w.+-]+@[\w-]+\.[\w.-]+/i', '', $txt) ?? $txt;
        $txt = preg_replace('/\+?351[\s.-]?\d{3}[\s.-]?\d{3}[\s.-]?\d{3}/', '', $txt) ?? $txt;
        $txt = preg_replace('/\b\d{9}\b/', '', $txt) ?? $txt;
        $txt = preg_replace('/(?:€\s?\d[\d .,]*|\d[\d .,]*\s?€|\d+[.,]\d{2}\s?(eur|euros?))/iu', '', $txt) ?? $txt;
        $txt = trim(preg_replace('/\s{2,}/', ' ', $txt) ?? $txt, ' -–—|,;');

        $corrected = $hadHtml || $txt !== $before;

        if (mb_strlen($txt) < 20) {
            $cat = TitleBuilder::clean((string) $product->category_name);
            $txt = trim(TitleBuilder::clean($product->name).($cat ? ' — '.$cat : ''), ' —');
            $txt .= '. Disponível na '.MerchantPolicy::brandFallback().', com entrega em todo o Portugal.';
            $corrected = true;
        }

        if (mb_strlen($txt) < 500) {
            $txt = $this->expandDescription($product, $txt);
        }

        return [$txt, $corrected ? 'description nettoyée' : null];
    }

    private function expandDescription(Product $product, string $txt): string
    {
        $bits = [$txt];
        $specs = $product->relationLoaded('specs') ? $product->specs : collect();
        foreach ($specs->take(8) as $spec) {
            $attr = TitleBuilder::clean((string) $spec->attr);
            $value = TitleBuilder::clean((string) $spec->value);
            if ($attr !== '' && $value !== '' && $value !== '0') {
                $bits[] = $attr.': '.$value.'.';
            }
        }

        $bits[] = 'Envio gratuito para todo o Portugal. Entrega em 1 a 3 dias úteis.';

        return trim(preg_replace('/\s+/u', ' ', implode(' ', $bits)) ?? implode(' ', $bits));
    }

    /**
     * @return array{id: int, path: string}
     */
    private function googleCategory(Product $product): array
    {
        $hay = Str::lower(($product->category_name ?? '').' '.($product->category_slug ?? '').' '.$product->name);
        $key = match (true) {
            Str::contains($hay, ['sofa', 'sofá', 'cadeirão', 'cadeirao', 'poltrona', 'chaise']) => 'sofas',
            Str::contains($hay, ['colchão', 'colchao', 'colchões', 'colchoes', 'mattress']) => 'mattress',
            Str::contains($hay, ['mesa de cabeceira', 'mesas de cabeceira', 'mesita', 'nightstand']) => 'nightstand',
            Str::contains($hay, ['estrado', 'sommier', 'cama ', 'camas ', 'bed frame']) => 'bedframe',
            Str::contains($hay, ['roupeiro', 'armário', 'armario', 'wardrobe']) => 'wardrobe',
            Str::contains($hay, ['mesa', 'table', 'aparador', 'consola']) => 'table',
            Str::contains($hay, ['cadeira', 'chair', 'banco']) => 'chair',
            Str::contains($hay, ['espelho', 'mirror']) => 'mirror',
            Str::contains($hay, ['tapete', 'carpete', 'rug']) => 'rug',
            Str::contains($hay, ['candeeiro', 'lamp', 'iluminação', 'iluminacao']) => 'lighting',
            default => 'furniture',
        };

        return self::GCATEGORY[$key];
    }

    private function productType(Product $product): string
    {
        $crumbs = collect($product->breadcrumb ?: [])
            ->map(fn ($c) => TitleBuilder::clean((string) (Arr::get($c, 'nome') ?? Arr::get($c, 'name') ?? '')))
            ->filter()
            ->values();

        return $crumbs->isNotEmpty() ? $crumbs->implode(' > ') : TitleBuilder::clean((string) $product->category_name);
    }

    /**
     * Include color/size only when every variant shares a single value (parent-level feed item).
     *
     * @param  list<string>  $keys
     */
    private function singleAttribute(Product $product, array $keys): ?string
    {
        $variations = $product->variations['variations'] ?? [];
        if (! is_array($variations) || $variations === []) {
            return null;
        }

        $values = [];
        foreach ($variations as $row) {
            if (! is_array($row)) {
                continue;
            }
            foreach ($row as $key => $opt) {
                if (! in_array(strtoupper((string) $key), array_map('strtoupper', $keys), true)) {
                    continue;
                }
                $name = is_array($opt)
                    ? (string) ($opt['name'] ?? $opt['color'] ?? '')
                    : (string) $opt;
                $name = TitleBuilder::clean($name);
                if ($name !== '') {
                    $values[$name] = true;
                }
            }
        }

        return count($values) === 1 ? array_key_first($values) : null;
    }

    private function assetUrl(string $path): string
    {
        $path = ltrim($path, '/');
        if (Str::startsWith($path, ['http://', 'https://'])) {
            return $path;
        }

        $encoded = implode('/', array_map('rawurlencode', explode('/', $path)));

        return MerchantPolicy::baseUrl().'/'.$encoded;
    }

    private function isPlaceholder(?string $path): bool
    {
        if (! $path) {
            return true;
        }
        $b = Str::lower(basename($path));

        return Str::contains($b, ['placeholder', 'no-image', 'noimage', 'sem-imagem', 'default', 'logo']);
    }

    private function matchesRestricted(string $haystack): bool
    {
        foreach (self::RESTRICTED as $pattern) {
            if (preg_match('/\b'.$pattern.'\b/u', $haystack)) {
                return true;
            }
        }

        return false;
    }

    private function looksCorrectedCasing(string $raw, string $built): bool
    {
        return mb_strtoupper($raw, 'UTF-8') === $raw && $built !== $raw;
    }
}
