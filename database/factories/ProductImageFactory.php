<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProductImage>
 */
class ProductImageFactory extends Factory
{
    protected $model = ProductImage::class;

    public function definition(): array
    {
        $file = 'foto-teste-'.fake()->unique()->numerify('######').'.jpg';

        return [
            'product_id' => Product::factory(),
            'filename' => $file,
            'path' => 'assets/images/test-gmc/'.$file,
            'position' => 0,
        ];
    }
}
