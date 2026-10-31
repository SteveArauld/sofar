<?php

namespace App\Console\Commands;

use App\Support\CatalogTranslator;
use Illuminate\Console\Command;

/**
 * Réécrit database/data/categories.json et products.json en structure bilingue {pt, it}.
 */
class CatalogLocalizeJson extends Command
{
    protected $signature = 'catalog:localize-json
        {--path= : dossier data (défaut database/data)}
        {--dry-run : n\'écrit pas les fichiers}';

    protected $description = 'Ajoute les traductions IT (Spatie) dans products.json / categories.json';

    public function handle(): int
    {
        $base = rtrim($this->option('path') ?: database_path('data'), '/');
        $catFile = "$base/categories.json";
        $prodFile = "$base/products.json";

        if (! is_file($catFile) || ! is_file($prodFile)) {
            $this->error("Fichiers introuvables dans $base");

            return self::FAILURE;
        }

        $this->info('Localisation des catégories…');
        $categories = json_decode(file_get_contents($catFile), true) ?: [];
        foreach ($categories as &$c) {
            $c['name'] = CatalogTranslator::normalize($c['name'] ?? '', true) ?? ['pt' => '', 'it' => ''];
        }
        unset($c);

        $this->info('Localisation des produits (peut prendre 1–2 min)…');
        $products = json_decode(file_get_contents($prodFile), true) ?: [];
        $bar = $this->output->createProgressBar(count($products));
        $bar->start();

        foreach ($products as &$p) {
            // force=true : recalcule toujours l'IT depuis le PT (améliore le dictionnaire)
            $p['name'] = CatalogTranslator::normalize($p['name'] ?? 'Sem nome', true);
            $p['category_name'] = CatalogTranslator::normalize($p['category_name'] ?? null, true);
            foreach (['short_description_html', 'long_description_html', 'logistic_data_html', 'delivery_assembly_html'] as $field) {
                if (array_key_exists($field, $p)) {
                    $p[$field] = CatalogTranslator::normalize($p[$field], true);
                }
            }

            if (! empty($p['specs']) && is_array($p['specs'])) {
                $newSpecs = [];
                foreach ($p['specs'] as $attr => $value) {
                    $attrBi = CatalogTranslator::normalize((string) $attr, true);
                    $key = $attrBi['pt'] ?? (string) $attr;
                    $newSpecs[$key] = [
                        'attr' => $attrBi,
                        'value' => $value === null || $value === ''
                            ? null
                            : CatalogTranslator::normalize(is_array($value) && ! isset($value['pt'])
                                ? json_encode($value, JSON_UNESCAPED_UNICODE)
                                : $value, true),
                    ];
                }
                // Conserver une forme simple pour l'import : attr => value bilingue
                $flat = [];
                $p['_spec_attrs_i18n'] = [];
                foreach ($newSpecs as $row) {
                    $ptAttr = $row['attr']['pt'] ?? '';
                    $flat[$ptAttr] = $row['value'];
                    $p['_spec_attrs_i18n'][$ptAttr] = $row['attr'];
                }
                $p['specs'] = $flat;
            }

            if (! empty($p['extra_services']) && is_array($p['extra_services'])) {
                foreach ($p['extra_services'] as &$s) {
                    $nome = $s['nome'] ?? $s['name'] ?? null;
                    if ($nome !== null) {
                        $s['nome'] = CatalogTranslator::normalize($nome, true);
                        $s['name'] = $s['nome'];
                    }
                }
                unset($s);
            }

            if (! empty($p['breadcrumb']) && is_array($p['breadcrumb'])) {
                foreach ($p['breadcrumb'] as &$node) {
                    if (is_array($node) && isset($node['nome'])) {
                        $node['nome_i18n'] = CatalogTranslator::normalize($node['nome'], true);
                        $node['nome'] = $node['nome_i18n']['pt'] ?? $node['nome'];
                    }
                }
                unset($node);
            }

            $bar->advance();
        }
        unset($p);
        $bar->finish();
        $this->newLine();

        if ($this->option('dry-run')) {
            $this->warn('Dry-run : aucune écriture.');

            return self::SUCCESS;
        }

        // Backup une fois
        foreach ([$catFile, $prodFile] as $file) {
            $bak = $file.'.bak-pt';
            if (! is_file($bak)) {
                copy($file, $bak);
                $this->line("Backup : $bak");
            }
        }

        file_put_contents(
            $catFile,
            json_encode($categories, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)."\n"
        );
        file_put_contents(
            $prodFile,
            json_encode($products, JSON_UNESCAPED_UNICODE)."\n"
        );

        $this->info(sprintf(
            'OK : %d catégories, %d produits localisés (pt/it).',
            count($categories),
            count($products)
        ));

        return self::SUCCESS;
    }
}
