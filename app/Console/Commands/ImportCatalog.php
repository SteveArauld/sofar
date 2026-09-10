<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductExtraService;
use App\Models\ProductImage;
use App\Models\ProductReview;
use App\Models\ProductSpec;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ImportCatalog extends Command
{
    protected $signature = 'catalog:import
        {--path= : dossier contenant products.json / categories.json / images (défaut database/data)}
        {--fresh : vider les tables du catalogue avant import}
        {--no-images : ne pas copier les images}';

    protected $description = 'Importe le catalogue scrapé dans la base. Les images sont déjà servies depuis public/assets/images (aucune copie).';

    private int $synthetic = 900000; // ids de secours pour les catégories sans id source

    public function handle(): int
    {
        $base = rtrim($this->option('path') ?: database_path('data'), '/');
        $prodFile = "$base/products.json";
        $catFile  = "$base/categories.json";

        if (! is_file($prodFile) || ! is_file($catFile)) {
            $this->error("Fichiers introuvables dans $base (products.json / categories.json).");
            return self::FAILURE;
        }

        $categories = json_decode(file_get_contents($catFile), true) ?: [];
        $products   = json_decode(file_get_contents($prodFile), true) ?: [];
        $this->info(sprintf('Lecture : %d catégories, %d produits.', count($categories), count($products)));

        if ($this->option('fresh')) {
            $this->warn('Purge des tables catalogue…');
            DB::table('category_product')->delete();
            ProductImage::query()->delete();
            ProductSpec::query()->delete();
            ProductExtraService::query()->delete();
            ProductReview::query()->delete();
            Product::query()->delete();
            Category::query()->delete();
        }

        // ---- 1. Catégories : résoudre les ids (certaines ont id=null au scrap) ----
        $slugToId = [];
        foreach ($categories as $c) {
            $slug = $c['slug'] ?? null;
            if (! $slug) {
                continue;
            }
            $slugToId[$slug] = $c['id'] ?? ($slugToId[$slug] ?? ++$this->synthetic);
        }
        // produits : compléter les slugs de catégories absents du fichier categories.json
        foreach ($products as $p) {
            foreach (($p['breadcrumb'] ?? []) as $node) {
                $slug = $node['link'] ?? null;
                if ($slug && ! isset($slugToId[$slug])) {
                    $slugToId[$slug] = ++$this->synthetic;
                }
            }
            foreach (($p['categories'] ?? []) as $slug) {
                if ($slug && ! isset($slugToId[$slug])) {
                    $slugToId[$slug] = ++$this->synthetic;
                }
            }
        }

        $catBySlug = [];
        foreach ($categories as $c) {
            $catBySlug[$c['slug']] = $c;
        }

        foreach ($slugToId as $slug => $id) {
            $c = $catBySlug[$slug] ?? ['slug' => $slug, 'name' => Str::of($slug)->replace('-', ' ')->title()];
            $parentSlug = $c['parent_slug'] ?? null;
            Category::updateOrCreate(
                ['id' => $id],
                [
                    'name'        => $c['name'] ?? $slug,
                    'slug'        => $slug,
                    'parent_id'   => $parentSlug ? ($slugToId[$parentSlug] ?? null) : null,
                    'parent_slug' => $parentSlug,
                    'level'       => $c['level'] ?? 1,
                    'url'         => $c['url'] ?? null,
                    'catalog_url' => $c['catalog_url'] ?? null,
                ]
            );
        }
        $this->info(Category::count().' catégories en base.');

        // ---- 2. Produits ----
        $bar = $this->output->createProgressBar(count($products));
        $bar->start();
        $imgSrcDir = is_dir("$base/images") ? "$base/images" : null;
        $copyImages = ! $this->option('no-images');

        foreach ($products as $p) {
            DB::transaction(function () use ($p, $slugToId, $imgSrcDir, $copyImages) {
                $catSlug = $p['category_slug'] ?? null;

                $product = Product::updateOrCreate(
                    ['id' => $p['id']],
                    [
                        'erp_id'              => $p['erp_id'] ?? null,
                        'name'                => $p['name'] ?? 'Sans nom',
                        'slug'                => $p['slug'] ?? Str::slug($p['name'] ?? $p['id']),
                        'url'                 => $p['url'] ?? null,
                        'sku'                 => $p['sku'] ?? null,
                        'ean'                 => $p['ean'] ?? null,
                        'brand'               => $p['brand'] ?? null,
                        'supplier_id'         => $p['supplier_id'] ?? null,
                        'category_id'         => $catSlug ? ($slugToId[$catSlug] ?? null) : null,
                        'category_name'       => $p['category_name'] ?? null,
                        'category_slug'       => $catSlug,
                        'price'               => $p['price'] ?? null,
                        'price_before'        => $p['price_before'] ?? null,
                        'currency'            => $p['currency'] ?? 'EUR',
                        'vat_percent'         => $p['vat_percent'] ?? null,
                        'availability'        => $p['availability'] ?? null,
                        'in_stock'            => (bool) ($p['in_stock'] ?? false),
                        'stock'               => $p['stock'] ?? null,
                        'stock_global'        => $p['stock_global'] ?? null,
                        'delivery_time_stock' => $p['delivery_time_stock'] ?? null,
                        'delivery_time_order' => $p['delivery_time_order'] ?? null,
                        'manufacturing_days'  => $p['manufacturing_days'] ?? null,
                        'short_description_html' => $p['short_description_html'] ?? null,
                        'long_description_html'  => $p['long_description_html'] ?? null,
                        'logistic_data_html'     => $p['logistic_data_html'] ?? null,
                        'delivery_assembly_html' => $p['delivery_assembly_html'] ?? null,
                        'dimensions'          => $p['dimensions'] ?? null,
                        'variations'          => $p['variations'] ?? null,
                        'flags'               => $p['flags'] ?? null,
                        'breadcrumb'          => $p['breadcrumb'] ?? null,
                        'category_path'       => $p['category_path'] ?? null,
                        'rating'              => $p['rating'] ?? null,
                        'views_20d'           => $p['views_20d'] ?? null,
                        'status'              => $p['status'] ?? null,
                        'source_created_at'   => $p['created_at'] ?? null,
                        'source_updated_at'   => $p['updated_at'] ?? null,
                        'images_count'        => $p['images_count'] ?? count($p['images'] ?? []),
                        'source_payload'      => $p,
                    ]
                );

                // pivot catégories : fil d'Ariane + toutes les catégories où le
                // produit apparaît sur le site (multi-appartenance).
                $sync = [];
                foreach (($p['category_path'] ?? []) as $depth => $slug) {
                    if (isset($slugToId[$slug])) {
                        $sync[$slugToId[$slug]] = ['depth' => $depth];
                    }
                }
                foreach (($p['categories'] ?? []) as $slug) {
                    if (isset($slugToId[$slug]) && ! isset($sync[$slugToId[$slug]])) {
                        $sync[$slugToId[$slug]] = ['depth' => 0];
                    }
                }
                $product->categories()->sync($sync);

                // images : servies directement depuis public/assets/images (aucune copie).
                // On enregistre seulement le chemin, relatif à public/.
                $product->images()->delete();
                $pos = 0;
                foreach (($p['images'] ?? []) as $img) {
                    $rel = ltrim($img['local_path'] ?? '', '/');
                    // normalise vers "assets/images/<cat>/<prod>/<file>"
                    $rel = preg_replace('#^images/#', 'assets/images/', $rel);
                    if (! str_starts_with($rel, 'assets/images/')) {
                        $rel = 'assets/images/'.$rel;
                    }

                    // copie optionnelle : seulement si --path pointe vers un dossier
                    // contenant encore les fichiers sources
                    if ($copyImages && $imgSrcDir) {
                        $src = $imgSrcDir.'/'.preg_replace('#^assets/images/#', '', $rel);
                        if (is_file($src)) {
                            $dest = public_path($rel);
                            @mkdir(dirname($dest), 0775, true);
                            if (! is_file($dest) || filesize($dest) !== filesize($src)) {
                                copy($src, $dest);
                            }
                        }
                    }

                    ProductImage::create([
                        'product_id' => $product->id,
                        'filename'   => $img['filename'] ?? basename($rel),
                        'path'       => $rel,
                        'source_url' => $img['source_url'] ?? null,
                        'position'   => $pos++,
                    ]);
                }

                // specs (dict attribut -> valeur)
                $product->specs()->delete();
                $sp = 0;
                foreach (($p['specs'] ?? []) as $attr => $value) {
                    if (is_array($value)) {
                        $value = json_encode($value, JSON_UNESCAPED_UNICODE);
                    }
                    ProductSpec::create([
                        'product_id' => $product->id,
                        'attr'       => (string) $attr,
                        'value'      => $value === null ? null : (string) $value,
                        'position'   => $sp++,
                    ]);
                }

                // services extra
                $product->extraServices()->delete();
                foreach (($p['extra_services'] ?? []) as $s) {
                    ProductExtraService::create([
                        'product_id'   => $product->id,
                        'name'         => $s['nome'] ?? 'Serviço',
                        'price'        => $s['preco'] ?? null,
                        'variation'    => $s['variation'] ?? null,
                        'protec_id'    => $s['protec_id'] ?? null,
                        'extra_erpid'  => $s['extra_erpid'] ?? null,
                        'protec_erpid' => $s['protec_erpid'] ?? null,
                    ]);
                }

                // avis
                $product->reviews()->delete();
                foreach (($p['reviews'] ?? []) as $r) {
                    ProductReview::create([
                        'product_id'  => $product->id,
                        'author'      => $r['author'] ?? $r['nome'] ?? null,
                        'rating'      => $r['rating'] ?? $r['nota'] ?? null,
                        'body'        => $r['body'] ?? $r['texto'] ?? null,
                        'reviewed_at' => $r['created'] ?? $r['data'] ?? null,
                        'raw'         => $r,
                    ]);
                }
            });
            $bar->advance();
        }
        $bar->finish();
        $this->newLine(2);
        $this->info(sprintf(
            'Import terminé : %d produits, %d images, %d specs.',
            Product::count(), ProductImage::count(), ProductSpec::count()
        ));

        return self::SUCCESS;
    }
}
