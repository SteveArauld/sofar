<?php

namespace App\Console\Commands;

use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

/**
 * Remet la colonne `products.brand` en conformité Google Merchant Center :
 * - conserve les marques fabricant réelles (literie : COLMED, MOLAFLEX, EMMA…),
 * - remplace l'ancien nom scrappé « Feira dos Sofás » et toute valeur générique
 *   ou vide par le nom de la boutique (config feed.store_brand).
 *
 * Idempotent : peut être relancé sans effet de bord.
 */
class NormalizeCatalogBrands extends Command
{
    protected $signature = 'catalog:normalize-brands {--dry-run : Affiche les changements sans écrire}';

    protected $description = 'Normalise products.brand pour Google Merchant Center';

    public function handle(): int
    {
        $store = (string) config('feed.store_brand', 'DFP Interiores');
        $real = collect(config('feed.real_brands', []))
            ->map(fn ($b) => Str::lower(trim($b)));

        $generic = ['sem marca', 'sin marca', 'genérico', 'generico', 'oem', 'n/a', 'na', '-', 'feira dos sofás', 'feira dos sofas'];

        $changed = 0;
        $total = 0;

        Product::query()->orderBy('id')->chunkById(500, function ($products) use (&$changed, &$total, $store, $real, $generic) {
            foreach ($products as $p) {
                $total++;
                $current = trim((string) $p->brand);
                $low = Str::lower($current);

                $keep = $real->contains($low);
                $isGeneric = $current === '' || in_array($low, $generic, true) || Str::contains($low, 'feira dos sof');

                $target = $keep ? $current : ($isGeneric ? $store : $current);

                // Cas restant : marque inconnue non générique -> on garde, mais on
                // signale pour revue manuelle.
                if (! $keep && ! $isGeneric && $current !== '') {
                    $this->warn("  ? id={$p->id} marque inconnue conservée : « {$current} »");

                    continue;
                }

                if ($target !== $current) {
                    $changed++;
                    if ($this->option('dry-run')) {
                        $this->line("  #{$p->id} : « {$current} » -> « {$target} »");
                    } else {
                        $p->forceFill(['brand' => $target])->saveQuietly();
                    }
                }
            }
        });

        $this->info(($this->option('dry-run') ? '[dry-run] ' : '')."{$changed} / {$total} produits mis à jour (brand).");

        return self::SUCCESS;
    }
}
