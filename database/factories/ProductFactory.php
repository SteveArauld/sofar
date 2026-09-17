<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        $id = fake()->unique()->numberBetween(800000, 899999);

        return [
            'id' => $id,
            'erp_id' => (string) $id,
            'name' => 'Sofá Teste GMC '.$id,
            'slug' => 'sofa-teste-gmc-'.$id,
            'sku' => 'SKU'.$id,
            'ean' => null,
            'brand' => 'DFP Interiores',
            'category_name' => 'Sofás',
            'category_slug' => 'sofas',
            'price' => 199.00,
            'price_before' => 299.00,
            'currency' => 'EUR',
            'vat_percent' => 23,
            'in_stock' => true,
            'stock' => 10,
            'short_description_html' => '<p>Sofá de teste para o Google Merchant Center, estofado em tecido, adequado para sala de estar.</p>',
            'long_description_html' => '<p>Descrição longa do sofá de teste sem URLs nem preços. Conforto e estrutura em madeira para utilização diária na sala.</p>',
            'variations' => ['options' => [], 'variations' => []],
            'flags' => [],
            'breadcrumb' => [['nome' => 'SOFÁS', 'link' => 'sofas']],
            'images_count' => 1,
        ];
    }
}
