<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Convertit les champs catalogue en JSON bilingue {pt, it} pour Spatie Translatable.
 * Les valeurs existantes (portugais) sont encapsulées ; l'italien est ajouté à l'import.
 */
return new class extends Migration
{
    public function up(): void
    {
        $this->wrapColumn('categories', 'name');

        foreach ([
            'name',
            'category_name',
            'short_description_html',
            'long_description_html',
            'logistic_data_html',
            'delivery_assembly_html',
        ] as $column) {
            $this->wrapColumn('products', $column);
        }

        $this->wrapColumn('product_specs', 'attr');
        $this->wrapColumn('product_specs', 'value');
        $this->wrapColumn('product_extra_services', 'name');
    }

    public function down(): void
    {
        // Irréversible proprement sans perte : on laisse les colonnes JSON.
    }

    private function wrapColumn(string $table, string $column): void
    {
        if (! Schema::hasTable($table) || ! Schema::hasColumn($table, $column)) {
            return;
        }

        $driver = Schema::getConnection()->getDriverName();
        $rows = DB::table($table)->select('id', $column)->orderBy('id')->get();

        foreach ($rows as $row) {
            $raw = $row->{$column};
            if ($raw === null || $raw === '') {
                continue;
            }

            // Déjà JSON bilingue ?
            if (is_string($raw) && str_starts_with(ltrim($raw), '{')) {
                $decoded = json_decode($raw, true);
                if (is_array($decoded) && (isset($decoded['pt']) || isset($decoded['it']))) {
                    continue;
                }
            }

            $payload = json_encode(['pt' => (string) $raw, 'it' => (string) $raw], JSON_UNESCAPED_UNICODE);

            if ($driver === 'sqlite') {
                // SQLite : colonnes texte → on stocke le JSON en texte.
                DB::table($table)->where('id', $row->id)->update([$column => $payload]);
            } else {
                DB::table($table)->where('id', $row->id)->update([$column => $payload]);
            }
        }
    }
};
