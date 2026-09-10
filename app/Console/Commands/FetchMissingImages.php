<?php

namespace App\Console\Commands;

use App\Models\ProductImage;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

/**
 * Télécharge les fichiers image manquants dans public/ à partir de `source_url`.
 * Utile après un scrape lancé avec --no-images : les lignes product_images
 * existent mais les nouveaux fichiers photo ne sont pas sur le disque.
 *
 *   php artisan catalog:fetch-images
 *   php artisan catalog:fetch-images --limit=200
 */
class FetchMissingImages extends Command
{
    protected $signature = 'catalog:fetch-images {--limit=0 : nombre max de fichiers} {--timeout=40}';

    protected $description = 'Télécharge les images produit absentes du disque (depuis source_url).';

    /** Réponse « sem imagem » du serveur source, à ne pas enregistrer. */
    private const PLACEHOLDER_MD5 = 'ea548401ff2db836f0caec2cde79ace2';

    public function handle(): int
    {
        $limit = (int) $this->option('limit');
        $timeout = (int) $this->option('timeout');

        $rows = ProductImage::query()
            ->whereNotNull('source_url')
            ->where('source_url', '!=', '')
            ->orderBy('id')
            ->cursor();

        $ok = $skip = $ph = $fail = 0;

        foreach ($rows as $img) {
            $dest = public_path($img->path);
            if (is_file($dest) && filesize($dest) > 0) {
                $skip++;
                continue;
            }

            if ($limit && ($ok + $ph + $fail) >= $limit) {
                break;
            }

            try {
                $res = Http::withHeaders(['User-Agent' => 'Mozilla/5.0'])
                    ->timeout($timeout)->retry(2, 500)->get($img->source_url);
            } catch (\Throwable $e) {
                $fail++;
                $this->warn("  ✗ {$img->filename} ({$e->getMessage()})");
                continue;
            }

            if (! $res->ok() || $res->body() === '') {
                $fail++;
                continue;
            }

            if (md5($res->body()) === self::PLACEHOLDER_MD5) {
                $ph++;   // la source n'a pas de vraie photo
                continue;
            }

            @mkdir(dirname($dest), 0775, true);
            file_put_contents($dest, $res->body());
            $ok++;

            if ($ok % 100 === 0) {
                $this->info("  {$ok} téléchargées…");
            }
        }

        $this->newLine();
        $this->info("Terminé : {$ok} téléchargées, {$ph} sans photo (placeholder), {$fail} échecs, {$skip} déjà présentes.");

        return self::SUCCESS;
    }
}
