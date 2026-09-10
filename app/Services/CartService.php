<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

/**
 * Panier stocké en session (clé "cart").
 * Chaque ligne : linhaid, product_id, variation (pid|null), qtd, preco, nome, seo, image, variation_label.
 */
class CartService
{
    private const KEY = 'cart';

    /** @return array<string,array> */
    public function lines(): array
    {
        return (array) session(self::KEY, []);
    }

    private function save(array $lines): void
    {
        session([self::KEY => $lines]);
    }

    public function count(): int
    {
        return array_sum(array_map(fn ($l) => (int) $l['qtd'], $this->lines()));
    }

    /** Résout un identifiant produit : entier brut ou token base64 "id:N". */
    public static function resolveProductId(?string $raw): ?int
    {
        $raw = trim((string) $raw);
        if ($raw === '') {
            return null;
        }
        if (ctype_digit($raw)) {
            return (int) $raw;
        }
        $decoded = base64_decode($raw, true);
        if ($decoded && Str::startsWith($decoded, 'id:')) {
            return (int) substr($decoded, 3);
        }
        return null;
    }

    public function add(int $productId, ?int $variation = null, int $qtd = 1): void
    {
        $product = Product::with('images')->find($productId);
        if (! $product) {
            return;
        }

        $qtd = max(1, $qtd);
        $variations = (array) data_get($product->source_payload, 'variations.variations', []);
        $row = null;
        foreach ($variations as $v) {
            if ((string) data_get($v, 'pid') === (string) $variation) {
                $row = $v;
                break;
            }
        }

        $preco = $row ? (float) data_get($row, 'preco') : (float) $product->price;
        if ($preco <= 0) {
            $preco = (float) $product->price;
        }

        // libellé lisible de la variation (MEDIDA, COR, ...)
        $label = [];
        if ($row) {
            foreach ($row as $k => $val) {
                if (in_array($k, ['pid', 'preco', 'precoantes', 'precopromo', 'stock', 'stocklojas',
                    'lojascomstock', 'prazo_entrega', 'seguros', 'image', 'images', 'imperline'], true)) {
                    continue;
                }
                $name = data_get($val, 'name', is_scalar($val) ? $val : null);
                if ($name !== null && $name !== '') {
                    $label[] = [$k => $name];
                }
            }
        }

        $img = optional($product->images->first())->filename;

        $lines = $this->lines();
        $key = $productId.':'.($variation ?? '0');

        if (isset($lines[$key])) {
            $lines[$key]['qtd'] += $qtd;
        } else {
            $lines[$key] = [
                'linhaid'     => $key,
                'product_id'  => $productId,
                'variation'   => $variation,
                'qtd'         => $qtd,
                'preco'       => $preco,
                'nome'        => $product->name,
                'seo'         => $product->slug,
                'image'       => $img ? 'products/'.$img : null,
                'variation_label' => $label,
            ];
        }

        $this->save($lines);
    }

    public function update(string $linhaid, int $qtd): void
    {
        $lines = $this->lines();
        if (! isset($lines[$linhaid])) {
            return;
        }
        if ($qtd <= 0) {
            unset($lines[$linhaid]);
        } else {
            $lines[$linhaid]['qtd'] = $qtd;
        }
        $this->save($lines);
    }

    public function remove(string $linhaid): void
    {
        $lines = $this->lines();
        unset($lines[$linhaid]);
        $this->save($lines);
    }

    public function clear(): void
    {
        session()->forget(self::KEY);
    }

    /** Forme attendue par le JS de la boutique (#_Global, carrinho.blade). */
    public function toArray(): array
    {
        $items = [];
        $subtotal = 0.0;

        foreach ($this->lines() as $l) {
            $total = round($l['preco'] * $l['qtd'], 2);
            $subtotal += $total;
            $items[] = [
                'linhaid'            => $l['linhaid'],
                'nome'               => $l['nome'],
                'seo'                => $l['seo'],
                'images'             => [$l['image'] ?: 'Desconto.jpg'],
                'preco'              => (float) $l['preco'],
                'qtd'                => (int) $l['qtd'],
                'total'              => number_format($total, 2, '.', ''),
                'variation'          => $l['variation_label'] ?: null,
                'valor_opcionais'    => 0,
                'stock'              => 999,
                'vende_apenas_stock' => 'N',
                'personalizavel'     => false,
                'Customtxt'          => null,
                'extra'              => null,
                'desconto'           => null,
            ];
        }

        return [
            'desconto'        => 0,
            'subtotal'        => round($subtotal, 2),
            'total'           => round($subtotal, 2),
            'items'           => $items,
            'cupao'           => null,
            'tipos_pagamento' => [],
            'tipos_entrega'   => [],
            'prazo_entrega'   => [],
        ];
    }

    /** dataLayer minimal (le JS fait dataLayer.push(JSON.parse(dl))). */
    public function dataLayer(string $event = 'add_to_cart'): string
    {
        return json_encode([
            'event'    => $event,
            'event_id' => Str::random(10),
        ]);
    }
}
