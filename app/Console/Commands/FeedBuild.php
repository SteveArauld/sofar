<?php

namespace App\Console\Commands;

use App\Domain\Merchant\GoogleFeedGenerator;
use App\Domain\Merchant\GoogleProductMapper;
use App\Domain\Merchant\Support\PriceFormatter;
use App\DTO\Merchant\GoogleProductData;
use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Builds the Google Merchant Center feed (Portugal).
 *
 *  php artisan feed:build
 *  php artisan merchant:google-feed
 */
class FeedBuild extends Command
{
    protected $signature = 'feed:build {--limit= : Override config(feed.target_items)}';

    protected $description = 'Construit le flux Google Merchant Center conforme';

    public function handle(GoogleProductMapper $mapper, GoogleFeedGenerator $generator): int
    {
        $startedAt = microtime(true);
        $min = (float) config('feed.min_price', 80);
        $target = (int) ($this->option('limit') ?: config('feed.target_items', 980));
        $safetyFloor = (int) config('feed.min_items_safety', 500);

        $this->info('Base URL : '.config('feed.base_url'));
        $this->info("Prix minimum : {$min} € · cible : {$target} items");

        $rows = [];
        $eligible = [];
        $seen = [];

        Product::query()
            ->with(['images', 'specs'])
            ->orderBy('id')
            ->chunk(300, function ($chunk) use (&$rows, &$eligible, &$seen, $mapper) {
                foreach ($chunk as $product) {
                    [$row, $item] = $mapper->evaluateForFeed($product, $seen);
                    $rows[$product->id] = $row;
                    if ($item !== null) {
                        $eligible[] = $item;
                    }
                }
            });

        usort($eligible, function (GoogleProductData $a, GoogleProductData $b) {
            return [$b->qualityId, $b->qualityImg, $b->qualityDesc, $b->offerPriceValue()]
                <=> [$a->qualityId, $a->qualityImg, $a->qualityDesc, $a->offerPriceValue()];
        });

        $selected = array_slice($eligible, 0, $target);
        $overflow = array_slice($eligible, $target);

        foreach ($selected as $rank => $item) {
            $id = (int) $item->id;
            $rows[$id]['status'] = 'INCLUS';
            $rows[$id]['rank'] = $rank + 1;
        }
        foreach ($overflow as $item) {
            $id = (int) $item->id;
            $rows[$id]['status'] = 'EXCLU';
            $rows[$id]['reason'] = trim(($rows[$id]['reason'] ? $rows[$id]['reason'].' ; ' : '').'éligible non inclus (au-delà de la cible)');
        }

        // Safety floor: a run that qualifies far fewer products than usual is
        // more likely a bug (broken eligibility check, partial DB outage) than
        // a genuine catalog collapse. Never let it overwrite a working feed.
        if (count($selected) < $safetyFloor) {
            $message = sprintf(
                'Génération refusée : seulement %d produits éligibles (seuil de sécurité %d, cible %d). Les fichiers publics existants ne sont pas modifiés.',
                count($selected),
                $safetyFloor,
                $target
            );
            $this->error($message);
            Log::error('Google Merchant Feed generation failed: safety floor not met', [
                'eligible_count' => count($selected),
                'safety_floor' => $safetyFloor,
                'target' => $target,
                'total_products_scanned' => count($rows),
            ]);

            return self::FAILURE;
        }

        $storage = storage_path('app/feeds/google-shopping.xml');

        try {
            $generator->write($selected, $storage);

            $publicDir = public_path('feeds');
            if (! is_dir($publicDir) && ! mkdir($publicDir, 0775, true) && ! is_dir($publicDir)) {
                throw new \RuntimeException("Impossible de créer le répertoire public : {$publicDir}");
            }

            $this->publishAtomic($storage, public_path('feeds/google-merchant.xml'));
            $this->publishAtomic($storage, public_path('dfpinteriores-gmc-conforme.xml'));
        } catch (Throwable $e) {
            $this->error('Génération du flux échouée : '.$e->getMessage());
            Log::error('Google Merchant Feed generation failed: '.$e->getMessage(), [
                'eligible_count' => count($selected),
                'exception' => $e->getMessage(),
            ]);

            return self::FAILURE;
        }

        $this->writeCsv($selected);
        $this->writeSelectionCsv($rows);

        $durationMs = (int) round((microtime(true) - $startedAt) * 1000);
        $fileSize = is_file($storage) ? filesize($storage) : 0;

        $this->newLine();
        $this->info('Flux : public/feeds/google-merchant.xml ('.count($selected).' items)');
        $this->info('Copie : public/dfpinteriores-gmc-conforme.xml');
        $this->info('Storage : storage/app/feeds/google-shopping.xml');
        $this->info('Traçabilité : FEED-SELECTION.csv ('.count($rows).' lignes)');
        if (count($selected) < $target) {
            $this->warn('ATTENTION : '.count($selected)." items éligibles seulement (cible {$target}). Aucune compensation effectuée.");
        }
        if (count($overflow) > 0) {
            $this->line(count($overflow).' produits éligibles non inclus (listés EXCLU dans FEED-SELECTION.csv).');
        }

        Log::info("Google Merchant Feed generated successfully: {$fileSize} bytes, ".count($selected).' products', [
            'products' => count($selected),
            'total_scanned' => count($rows),
            'excluded_overflow' => count($overflow),
            'duration_ms' => $durationMs,
            'file_size_bytes' => $fileSize,
            'target' => $target,
        ]);

        return self::SUCCESS;
    }

    /**
     * Copies $source to a temp file next to $destination, validates it lands
     * fully on disk, then rename()s it into place. rename() on the same
     * filesystem is atomic, so a concurrent reader of $destination never sees
     * a truncated or half-copied file.
     */
    private function publishAtomic(string $source, string $destination): void
    {
        $tmp = dirname($destination).'/.'.basename($destination).'.'.getmypid().'.'.uniqid('', true).'.tmp';

        if (! copy($source, $tmp)) {
            throw new \RuntimeException("Échec de la copie vers le fichier temporaire : {$tmp}");
        }

        if (filesize($tmp) === false || filesize($tmp) !== filesize($source)) {
            @unlink($tmp);
            throw new \RuntimeException("Copie incomplète détectée pour : {$destination}");
        }

        if (! rename($tmp, $destination)) {
            @unlink($tmp);
            throw new \RuntimeException("Échec du remplacement atomique de : {$destination}");
        }
    }

    /**
     * @param  list<GoogleProductData>  $items
     */
    private function writeCsv(array $items): void
    {
        $fh = fopen(public_path('feeds/google-merchant.csv'), 'w');
        $cols = ['id', 'title', 'description', 'link', 'image_link', 'additional_image_link',
            'availability', 'condition', 'price', 'sale_price', 'brand', 'gtin', 'mpn',
            'identifier_exists', 'google_product_category', 'product_type', 'item_group_id',
            'shipping', 'min_handling_time', 'max_handling_time', 'min_transit_time', 'max_transit_time'];
        fputcsv($fh, $cols, "\t");
        foreach ($items as $item) {
            fputcsv($fh, [
                $item->id, $item->title, $item->description, $item->link, $item->imageLink,
                implode(',', $item->extraImages), $item->availability->value, $item->condition,
                $item->formattedPrice(), $item->formattedSalePrice() ?? '',
                $item->brand, $item->gtin ?? '', $item->mpn ?? '', $item->identifierExists ? 'yes' : 'no',
                $item->googleProductCategory, $item->productType, $item->itemGroupId ?? '',
                config('feed.target_country', 'PT').':::'.PriceFormatter::format((float) config('feed.shipping_price', 0)),
                (int) config('feed.handling_time.min', 1), (int) config('feed.handling_time.max', 1),
                (int) config('feed.transit_time.min', 0), (int) config('feed.transit_time.max', 2),
            ], "\t");
        }
        fclose($fh);
    }

    /**
     * @param  array<int, array<string, mixed>>  $rows
     */
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
}
