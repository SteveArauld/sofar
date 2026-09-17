<?php

namespace Tests\Unit\Merchant;

use App\Domain\Merchant\GoogleProductMapper;
use App\Domain\Merchant\Support\Availability;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GoogleProductMapperTest extends TestCase
{
    use RefreshDatabase;

    public function test_maps_stable_id_price_and_brand(): void
    {
        $product = Product::factory()->create([
            'id' => 3737,
            'name' => 'K51151M CADEIRÃO RELAX PROMO!!!',
            'slug' => 'k51151m-cadeirao-relax-3737',
            'sku' => '00033000000151',
            'brand' => 'Feira dos Sofás',
            'price' => 299,
            'price_before' => 799,
            'in_stock' => true,
        ]);
        $product->images()->create([
            'filename' => 'foto1.jpg',
            'path' => 'assets/images/test-gmc/foto1.jpg',
            'position' => 0,
        ]);
        $this->putJpeg(public_path('assets/images/test-gmc/foto1.jpg'));

        $dto = app(GoogleProductMapper::class)->mapForLanding($product->fresh('images'));

        $this->assertSame('3737', $dto->id);
        $this->assertSame('DFP Interiores', $dto->brand);
        $this->assertSame('00033000000151', $dto->mpn);
        $this->assertTrue($dto->identifierExists);
        $this->assertNull($dto->gtin);
        $this->assertSame(799.0, $dto->priceValue);
        $this->assertSame(299.0, $dto->salePriceValue);
        $this->assertSame('299.00', $dto->toJsonLd()['offers']['price']);
        $this->assertSame('EUR', $dto->toJsonLd()['offers']['priceCurrency']);
        $this->assertSame(Availability::InStock, $dto->availability);
        $this->assertStringNotContainsString('PROMO', $dto->title);
        $this->assertStringStartsWith('https://dfpinteriores.com/', $dto->link);
    }

    public function test_excludes_out_of_stock_from_feed(): void
    {
        $product = Product::factory()->create([
            'in_stock' => false,
            'price' => 199,
        ]);
        $product->images()->create([
            'filename' => 'foto1.jpg',
            'path' => 'assets/images/test-gmc/out.jpg',
            'position' => 0,
        ]);
        $this->putJpeg(public_path('assets/images/test-gmc/out.jpg'));

        $seen = [];
        [, $item] = app(GoogleProductMapper::class)->evaluateForFeed($product->fresh('images'), $seen);

        $this->assertNull($item);
    }

    private function putJpeg(string $path): void
    {
        $dir = dirname($path);
        if (! is_dir($dir)) {
            mkdir($dir, 0775, true);
        }
        file_put_contents($path, base64_decode('/9j/4AAQSkZJRgABAQAAAQABAAD/2wAAAQIBAQEBAQIBAQECAgICAgMCAgICAgMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwP/wAARCAABAAEDAREAAhEBAxEB/8QAFQABAQAAAAAAAAAAAAAAAAAAAAj/xAAUEAEAAAAAAAAAAAAAAAAAAAAA/8QAFQEBAQAAAAAAAAAAAAAAAAAAAAX/xAAUEQEAAAAAAAAAAAAAAAAAAAAA/9oADAMBAAIQAxAAAAGf/9k='));
    }
}
