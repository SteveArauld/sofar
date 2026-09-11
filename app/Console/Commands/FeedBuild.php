<?php

namespace App\Console\Commands;

use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

/**
 * Génère le flux produits Google Merchant Center conforme (marché Portugal).
 *
 *  Sorties :
 *   - public/feeds/google-merchant.xml         (RSS 2.0 + espace de noms g:)
 *   - public/feeds/google-merchant.csv         (secours, TSV)
 *   - public/dfpinteriores-gmc-conforme.xml    (copie, URL demandée)
 *   - FEED-SELECTION.csv                       (traçabilité : 1 ligne / produit du catalogue)
 *
 *  Étapes appliquées : exclusions strictes, détection de risque + correction
 *  automatique des cas moyens, tri qualité, sélection des N premiers
 *  (config feed.target_items, jamais dépassé, jamais compensé).
 */
class FeedBuild extends Command
{
    protected $signature = 'feed:build {--limit= : Force un plafond différent de config(feed.target_items)}';

    protected $description = 'Construit le flux Google Merchant Center conforme';

    private string $base;

    /** google_product_category : ID numériques taxonomie officielle Google. */
    private const GCATEGORY = [
        'sofas' => ['id' => 635,    'path' => 'Furniture > Sofas'],
        'mattress' => ['id' => 2696,   'path' => 'Furniture > Beds & Accessories > Mattresses'],
        'nightstand' => ['id' => 6349,   'path' => 'Furniture > Beds & Accessories > Nightstands'],
        'bedframe' => ['id' => 505764, 'path' => 'Furniture > Beds & Accessories > Bed Frames'],
        'wardrobe' => ['id' => 6350,   'path' => 'Furniture > Armoires & Wardrobes'],
        'table' => ['id' => 6362,   'path' => 'Furniture > Tables'],
        'chair' => ['id' => 443,    'path' => 'Furniture > Chairs'],
        'mirror' => ['id' => 6335,   'path' => 'Home & Garden > Decor > Mirrors'],
        'rug' => ['id' => 570,    'path' => 'Home & Garden > Decor > Rugs'],
        'lighting' => ['id' => 594,    'path' => 'Home & Garden > Lighting'],
        'furniture' => ['id' => 436,    'path' => 'Furniture'],
    ];

    /**
     * Catégories restreintes Google -> exclusion.
     * Motifs testés en \b...\b (mot entier) pour éviter les faux positifs
     * (ex. « ARMARIO », « ARMAZENAMENTO », « ARMANI » ne sont pas « arma »).
     */
    private const RESTRICTED = [
        'arma de fogo', 'armas de fogo', 'pistola de bala', 'pistola c\/balas', 'espingarda',
        'munição', 'munições', 'airsoft', 'r[eé]plica de arma',
        'whisky', 'vodka', 'gin ', 'rum ', 'tequila', 'aguardente',
        'tabaco', 'cigarro', 'cigarros', 'vape', 'nicotina',
        'suplemento alimentar', 'emagrecimento', 'medicamento', 'medicamentos',
        'f[aá]rmaco', 'viagra', 'desbloqueio de telem[oó]vel', 'unlock tool', 'iptv',
    ];

    /** Signaux promo interdits dans les titres. */
    private const PROMO_WORDS = ['promo', 'promoção', 'oferta', 'melhor preço', 'melhor preco', 'saldo', 'liquidação', 'desconto', 'grátis', 'gratis', 'sale', '!!!'];

    public function handle(): int
    {
        $this->base = (string) config('feed.base_url');
        $min = (float) config('feed.min_price', 80);
        $target = (int) ($this->option('limit') ?: config('feed.target_items', 980));

        $this->info("Base URL : {$this->base}");
        $this->info("Prix minimum : {$min} € · cible : {$target} items");

        /** @var array<int,array> $rows traçabilité indexée par id produit */
        $rows = [];
        /** @var array<int,array> $eligible items prêts à écrire, avant tri/sélection */
        $eligible = [];
        $seen = [];   // dédoublonnage : hash titre+image+prix

        Product::query()
            ->with(['images'])
            ->orderBy('id')
            ->chunk(300, function ($chunk) use (&$rows, &$eligible, &$seen, $min) {
                foreach ($chunk as $p) {
                    [$row, $item] = $this->evaluate($p, $min, $seen);
                    $rows[$p->id] = $row;
                    if ($item !== null) {
                        $eligible[] = $item;
                    }
                }
            });

        // --- Tri qualité décroissante -------------------------------------------------
        usort($eligible, function ($a, $b) {
            return [$b['q_id'], $b['q_img'], $b['q_desc'], $b['price']]
               <=> [$a['q_id'], $a['q_img'], $a['q_desc'], $a['price']];
        });

        $selected = array_slice($eligible, 0, $target);
        $overflow = array_slice($eligible, $target);

        // marque le rang de tri + statut final dans la traçabilité
        foreach ($selected as $rank => $it) {
            $rows[$it['id']]['status'] = 'INCLUS';
            $rows[$it['id']]['rank'] = $rank + 1;
        }
        foreach ($overflow as $it) {
            $rows[$it['id']]['status'] = 'EXCLU';
            $rows[$it['id']]['reason'] = trim(($rows[$it['id']]['reason'] ? $rows[$it['id']]['reason'].' ; ' : '').'éligible non inclus (au-delà de la cible)');
        }

        $this->writeXml($selected);
        $this->writeCsv($selected);
        $this->writeSelectionCsv($rows);

        $this->newLine();
        $this->info('Flux : public/feeds/google-merchant.xml ('.count($selected).' items)');
        $this->info('Copie : public/dfpinteriores-gmc-conforme.xml');
        $this->info('Secours : public/feeds/google-merchant.csv');
        $this->info('Traçabilité : FEED-SELECTION.csv ('.count($rows).' lignes)');
        if (count($selected) < $target) {
            $this->warn('ATTENTION : '.count($selected)." items éligibles seulement (cible {$target}). Aucune compensation effectuée.");
        }
        if (count($overflow) > 0) {
            $this->line(count($overflow).' produits éligibles non inclus (listés EXCLU / « éligible non inclus » dans FEED-SELECTION.csv).');
        }

        return self::SUCCESS;
    }

    /**
     * Évalue un produit. Retourne [ligne traçabilité, item prêt à écrire|null].
     *
     * @return array{0:array,1:?array}
     */
    private function evaluate(Product $p, float $min, array &$seen): array
    {
        $row = [
            'id' => $p->id,
            'title' => (string) $p->name,
            'price' => $p->price,
            'status' => 'EXCLU',
            'reason' => '',
            'risk' => 'OK',
            'corrections' => '',
            'rank' => '',
        ];

        $price = (float) $p->price;
        $priceBefore = $p->price_before !== null ? (float) $p->price_before : null;
        $images = $p->images->pluck('path')->filter()->values();

        // ---------------------------------------------------------------- EXCLUSIONS
        $excl = [];
        if ($price <= 0) {
            $excl[] = 'prix nul / absent';
        }
        if ($price > 0 && $price < $min) {
            $excl[] = "prix < {$min} €";
        }
        if ($images->isEmpty()) {
            $excl[] = 'aucune image';
        }
        if (! $p->in_stock) {
            $excl[] = 'hors stock';
        }
        if ($this->isPlaceholder($images->first())) {
            $excl[] = 'image placeholder';
        }
        if ($this->matchesRestricted(Str::lower($p->name.' '.strip_tags((string) $p->short_description_html)))) {
            $excl[] = 'catégorie restreinte Google';
            $row['risk'] = 'RISCO ELEVADO';
        }
        // image sur disque
        $firstOnDisk = $images->first() && is_file(public_path($images->first()));
        if (! $firstOnDisk && $images->isNotEmpty()) {
            $excl[] = 'fichier image introuvable';
        }

        if ($excl) {
            $row['reason'] = implode(' ; ', $excl);

            return [$row, null];
        }

        // ---------------------------------------------------------------- RISQUE + CORRECTIONS
        $corr = [];
        $risk = 'OK';

        // Titre
        $title = $this->cleanText($p->name);
        if ($this->looksAllCaps($title)) {
            $title = $this->titleCase($title);
            $corr[] = 'titre normalisé (casse)';
            $risk = $this->raise($risk, 'MÉDIO');
        }
        if ($this->matchesAny(Str::lower($title), self::PROMO_WORDS) || Str::contains($title, ['!!!', '🔥', '⭐'])) {
            $title = $this->stripPromo($title);
            $corr[] = 'titre nettoyé (promo/emoji)';
            $risk = $this->raise($risk, 'MÉDIO');
        }
        $title = Str::limit($title, 150, '');
        if (mb_strlen($title) < 3) {
            $row['reason'] = 'titre inexploitable';
            $row['risk'] = 'RISCO ELEVADO';

            return [$row, null];
        }

        // Description
        [$description, $descCorr] = $this->buildDescription($p);
        if ($descCorr) {
            $corr[] = $descCorr;
            $risk = $this->raise($risk, 'MÉDIO');
        }

        // Marque
        $brand = trim((string) $p->brand) ?: (string) config('feed.store_brand');
        if (Str::contains(Str::lower($brand), 'feira dos sof') || $brand === '') {
            $brand = (string) config('feed.store_brand');
            $corr[] = 'marque corrigée';
            $risk = $this->raise($risk, 'MÉDIO');
        }

        // Identifiants
        $gtin = $this->normalizeGtin($p->ean);
        $mpn = $p->sku ? (string) $p->sku : null;
        if (! $gtin && ! $mpn && $brand === '') {
            $row['reason'] = 'aucun identifiant (gtin/mpn/brand)';
            $row['risk'] = 'RISCO ELEVADO';

            return [$row, null];
        }

        // Catégorie Google
        $gc = $this->googleCategory($p);

        // Dédoublonnage
        $hash = md5(Str::lower($title).'|'.basename((string) $images->first()).'|'.number_format($price, 2, '.', ''));
        if (isset($seen[$hash])) {
            $row['reason'] = 'doublon (titre + image + prix)';
            $row['risk'] = $risk;
            $row['corrections'] = implode(', ', $corr);

            return [$row, null];
        }
        $seen[$hash] = $p->id;

        // ---------------------------------------------------------------- ITEM
        $hasSale = $priceBefore !== null && $priceBefore > $price;
        $imgUrls = $images->map(fn ($path) => $this->assetUrl($path))->filter()->values();

        $item = [
            'id' => $p->id,
            'title' => $title,
            'description' => Str::limit($description, 5000, ''),
            'link' => $this->base.'/'.ltrim((string) $p->slug, '/'),
            'image_link' => $imgUrls->first(),
            'extra_images' => $imgUrls->slice(1, 10)->values()->all(),
            'availability' => 'in_stock',
            'price' => $hasSale ? $priceBefore : $price,
            'sale_price' => $hasSale ? $price : null,
            'brand' => $brand,
            'gtin' => $gtin,
            'mpn' => $mpn,
            'gcat_id' => $gc['id'],
            'gcat_path' => $gc['path'],
            'product_type' => $this->productType($p),
            'item_group_id' => $this->itemGroupId($p),
            // clés de tri qualité
            'q_id' => $gtin ? 2 : ($mpn ? 1 : 0),
            'q_img' => min($imgUrls->count(), 6),
            'q_desc' => min((int) floor(mb_strlen($description) / 200), 10),
        ];

        $row['status'] = 'INCLUS';
        $row['risk'] = $risk;
        $row['corrections'] = implode(', ', $corr);
        $row['title'] = $title;

        return [$row, $item];
    }

    // ------------------------------------------------------------------ helpers texte

    private function cleanText(?string $v): string
    {
        $v = html_entity_decode((string) $v, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $v = preg_replace('/\s+/u', ' ', $v);

        return trim((string) $v);
    }

    private function looksAllCaps(string $s): bool
    {
        if (! Str::contains($s, ' ')) {
            return false; // code produit court -> on laisse
        }
        $letters = preg_replace('/[^\p{L}]/u', '', $s);

        return $letters !== '' && mb_strtoupper($letters, 'UTF-8') === $letters;
    }

    private function titleCase(string $s): string
    {
        $s = mb_convert_case($s, MB_CASE_TITLE, 'UTF-8');

        // petits mots en minuscule
        return preg_replace_callback('/\b(De|Do|Da|Dos|Das|E|Em|Com|Para|A|O|As|Os|Ao|Aos)\b/u',
            fn ($m) => mb_strtolower($m[1], 'UTF-8'), $s);
    }

    private function stripPromo(string $s): string
    {
        $s = preg_replace('/[\x{1F000}-\x{1FAFF}\x{2600}-\x{27BF}]/u', '', $s);
        $s = preg_replace('/!{2,}/', '', $s);
        foreach (self::PROMO_WORDS as $w) {
            $s = preg_replace('/\b'.preg_quote($w, '/').'\b/iu', '', $s);
        }

        return trim(preg_replace('/\s{2,}/', ' ', $s), ' -–—|');
    }

    /** @return array{0:string,1:?string} [description, correction|null] */
    private function buildDescription(Product $p): array
    {
        $raw = $p->long_description_html ?: $p->short_description_html ?: '';
        $hadHtml = $raw !== strip_tags((string) $raw);
        $txt = $this->cleanText(strip_tags((string) $raw));

        $before = $txt;
        $txt = preg_replace('#https?://\S+#i', '', $txt);
        $txt = preg_replace('/[\w.+-]+@[\w-]+\.[\w.-]+/i', '', $txt);
        $txt = preg_replace('/\+?351[\s.-]?\d{3}[\s.-]?\d{3}[\s.-]?\d{3}/', '', $txt);
        $txt = preg_replace('/\b\d{9}\b/', '', $txt);
        $txt = preg_replace('/(?:€\s?\d[\d .,]*|\d[\d .,]*\s?€|\d+[.,]\d{2}\s?(eur|euros?))/iu', '', $txt);
        $txt = trim(preg_replace('/\s{2,}/', ' ', $txt), ' -–—|,;');

        $corrected = $hadHtml || $txt !== $before;

        if (mb_strlen($txt) < 20) {
            $cat = $this->cleanText((string) $p->category_name);
            $txt = trim($this->cleanText($p->name).($cat ? ' — '.$cat : ''), ' —');
            $txt .= '. Disponível na '.config('feed.store_brand').', com entrega em todo o Portugal.';
            $corrected = true;
        }

        return [$txt, $corrected ? 'description nettoyée' : null];
    }

    private function normalizeGtin($ean): ?string
    {
        $d = preg_replace('/\D/', '', (string) $ean);

        return ($d !== '' && in_array(strlen($d), [8, 12, 13, 14], true)) ? $d : null;
    }

    private function googleCategory(Product $p): array
    {
        $hay = Str::lower(($p->category_name ?? '').' '.($p->category_slug ?? '').' '.$p->name);
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

    private function productType(Product $p): string
    {
        $crumbs = collect($p->breadcrumb ?: [])
            ->map(fn ($c) => $this->cleanText((string) (Arr::get($c, 'nome') ?? Arr::get($c, 'name') ?? '')))
            ->filter()->values();

        return $crumbs->isNotEmpty() ? $crumbs->implode(' > ') : $this->cleanText((string) $p->category_name);
    }

    private function itemGroupId(Product $p): ?string
    {
        $v = $p->variations;

        return (is_array($v) && count($v) > 1) ? (string) $p->id : null;
    }

    private function assetUrl(string $path): string
    {
        $path = ltrim($path, '/');
        if (Str::startsWith($path, ['http://', 'https://'])) {
            return $path;
        }
        $encoded = implode('/', array_map('rawurlencode', explode('/', $path)));

        return $this->base.'/'.$encoded;
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

    private function matchesAny(string $haystack, array $needles): bool
    {
        foreach ($needles as $n) {
            if (Str::contains($haystack, $n)) {
                return true;
            }
        }

        return false;
    }

    private function raise(string $current, string $to): string
    {
        $order = ['OK' => 0, 'MÉDIO' => 1, 'RISCO ELEVADO' => 2];

        return ($order[$to] ?? 0) > ($order[$current] ?? 0) ? $to : $current;
    }

    // ------------------------------------------------------------------ écriture

    private function writeXml(array $items): void
    {
        $dir = public_path('feeds');
        if (! is_dir($dir)) {
            mkdir($dir, 0775, true);
        }
        $file = $dir.'/google-merchant.xml';
        $fh = fopen($file, 'w');

        $ht = (array) config('feed.handling_time');
        $tt = (array) config('feed.transit_time');

        fwrite($fh, '<?xml version="1.0" encoding="UTF-8"?>'."\n");
        fwrite($fh, '<rss version="2.0" xmlns:g="http://base.google.com/ns/1.0">'."\n  <channel>\n");
        fwrite($fh, '    <title>'.$this->esc(config('app.name')).' — Catálogo</title>'."\n");
        fwrite($fh, '    <link>'.$this->esc($this->base).'</link>'."\n");
        fwrite($fh, '    <description>Feed de produtos para Google Merchant Center (Portugal)</description>'."\n");
        fwrite($fh, '    <lastBuildDate>'.now()->toRfc2822String().'</lastBuildDate>'."\n");

        foreach ($items as $it) {
            fwrite($fh, "    <item>\n");
            fwrite($fh, $this->t('g:id', (string) $it['id']));
            fwrite($fh, $this->c('title', $it['title']));
            fwrite($fh, $this->c('description', $it['description']));
            fwrite($fh, $this->t('link', $it['link']));
            fwrite($fh, $this->t('g:image_link', $it['image_link']));
            foreach ($it['extra_images'] as $ex) {
                fwrite($fh, $this->t('g:additional_image_link', $ex));
            }
            fwrite($fh, $this->t('g:availability', $it['availability']));
            fwrite($fh, $this->t('g:condition', 'new'));
            fwrite($fh, $this->t('g:price', $this->money($it['price'])));
            if ($it['sale_price'] !== null) {
                fwrite($fh, $this->t('g:sale_price', $this->money($it['sale_price'])));
            }
            fwrite($fh, $this->c('g:brand', $it['brand']));
            if ($it['gtin']) {
                fwrite($fh, $this->t('g:gtin', $it['gtin']));
            }
            if ($it['mpn']) {
                fwrite($fh, $this->c('g:mpn', $it['mpn']));
            }
            fwrite($fh, $this->t('g:identifier_exists', $it['gtin'] ? 'yes' : 'no'));
            fwrite($fh, $this->t('g:google_product_category', (string) $it['gcat_id']));
            if ($it['product_type'] !== '') {
                fwrite($fh, $this->c('g:product_type', $it['product_type']));
            }
            if ($it['item_group_id']) {
                fwrite($fh, $this->t('g:item_group_id', $it['item_group_id']));
            }
            fwrite($fh, "      <g:shipping>\n");
            fwrite($fh, '        <g:country>PT</g:country>'."\n");
            fwrite($fh, '        <g:price>'.$this->esc($this->money(0)).'</g:price>'."\n");
            fwrite($fh, "      </g:shipping>\n");
            fwrite($fh, $this->t('g:min_handling_time', (string) ($ht['min'] ?? 1)));
            fwrite($fh, $this->t('g:max_handling_time', (string) ($ht['max'] ?? 2)));
            fwrite($fh, $this->t('g:min_transit_time', (string) ($tt['min'] ?? 1)));
            fwrite($fh, $this->t('g:max_transit_time', (string) ($tt['max'] ?? 2)));
            fwrite($fh, "    </item>\n");
        }

        fwrite($fh, "  </channel>\n</rss>\n");
        fclose($fh);

        copy($file, public_path('dfpinteriores-gmc-conforme.xml'));
    }

    private function writeCsv(array $items): void
    {
        $fh = fopen(public_path('feeds/google-merchant.csv'), 'w');
        $cols = ['id', 'title', 'description', 'link', 'image_link', 'additional_image_link',
            'availability', 'condition', 'price', 'sale_price', 'brand', 'gtin', 'mpn',
            'identifier_exists', 'google_product_category', 'product_type', 'item_group_id',
            'shipping', 'min_handling_time', 'max_handling_time', 'min_transit_time', 'max_transit_time'];
        fputcsv($fh, $cols, "\t");
        foreach ($items as $it) {
            fputcsv($fh, [
                $it['id'], $it['title'], $it['description'], $it['link'], $it['image_link'],
                implode(',', $it['extra_images']), $it['availability'], 'new',
                $this->money($it['price']), $it['sale_price'] !== null ? $this->money($it['sale_price']) : '',
                $it['brand'], $it['gtin'] ?? '', $it['mpn'] ?? '', $it['gtin'] ? 'yes' : 'no',
                $it['gcat_id'], $it['product_type'], $it['item_group_id'] ?? '',
                'PT:::'.$this->money(0), 1, 2, 1, 2,
            ], "\t");
        }
        fclose($fh);
    }

    private function writeSelectionCsv(array $rows): void
    {
        $fh = fopen(base_path('FEED-SELECTION.csv'), 'w');
        fputcsv($fh, ['id', 'titre', 'prix', 'statut', 'motif_exclusion', 'niveau_risque', 'corrections_appliquees', 'rang_tri']);
        ksort($rows);
        foreach ($rows as $r) {
            fputcsv($fh, [
                $r['id'], $r['title'], $r['price'], $r['status'],
                $r['reason'], $r['risk'], $r['corrections'], $r['rank'],
            ]);
        }
        fclose($fh);
    }

    private function money(float $v): string
    {
        return number_format($v, 2, '.', '').' EUR';
    }

    private function t(string $n, string $v): string
    {
        return '      <'.$n.'>'.$this->esc($v).'</'.$n.">\n";
    }

    private function c(string $n, string $v): string
    {
        $v = str_replace(']]>', ']]]]><![CDATA[>', $v);

        return '      <'.$n.'><![CDATA['.$v.']]></'.$n.">\n";
    }

    private function esc(string $v): string
    {
        return htmlspecialchars($v, ENT_QUOTES | ENT_XML1, 'UTF-8');
    }
}
