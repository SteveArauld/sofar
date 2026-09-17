<?php

namespace Tests\Feature\Merchant;

use App\Domain\Merchant\GoogleProductMapper;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GoogleFeedTest extends TestCase
{
    use RefreshDatabase;

    public function test_generated_xml_and_pdp_json_ld_share_the_same_offer(): void
    {
        $product = Product::factory()->create([
            'id' => 910001,
            'name' => 'Sofá Auditável Merchant',
            'slug' => 'sofa-auditavel-merchant-910001',
            'sku' => 'SKU910001',
            'brand' => 'DFP Interiores',
            'price' => 199.00,
            'price_before' => 299.00,
            'in_stock' => true,
            'category_name' => 'Sofás',
        ]);
        $rel = 'assets/images/test-gmc/sofa-910001.jpg';
        $product->images()->create([
            'filename' => 'sofa-910001.jpg',
            'path' => $rel,
            'position' => 0,
        ]);
        $this->putJpeg(public_path($rel));

        $this->artisan('merchant:google-feed')->assertSuccessful();

        $xml = file_get_contents(storage_path('app/feeds/google-shopping.xml'));
        $this->assertNotFalse($xml);
        $this->assertStringContainsString('xmlns:g="http://base.google.com/ns/1.0"', $xml);
        $this->assertStringContainsString('<g:id>910001</g:id>', $xml);
        $this->assertStringContainsString('<g:price>299.00 EUR</g:price>', $xml);
        $this->assertStringContainsString('<g:sale_price>199.00 EUR</g:sale_price>', $xml);
        $this->assertStringContainsString('<g:identifier_exists>yes</g:identifier_exists>', $xml);
        $this->assertStringContainsString('<g:country>PT</g:country>', $xml);
        $this->assertStringContainsString('<g:price>0.00 EUR</g:price>', $xml);
        $this->assertStringContainsString('<g:min_handling_time>1</g:min_handling_time>', $xml);
        $this->assertStringContainsString('<g:max_transit_time>2</g:max_transit_time>', $xml);

        $dto = app(GoogleProductMapper::class)->mapForLanding($product->fresh(['images', 'specs']));

        $response = $this->get('/'.$product->slug);
        $response->assertOk();
        $response->assertSee('<h1 class="ProdutoNome">', false);
        $response->assertSee('Sofá Auditável Merchant', false);
        $jsonLd = json_encode($dto->toJsonLd(), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        $response->assertSee($dto->toJsonLd()['offers']['price'], false);
        $response->assertSee('application/ld+json', false);
        $this->assertSame($dto->formattedPrice(), '299.00 EUR');
        $this->assertSame($dto->toJsonLd()['offers']['price'], '199.00');
        $this->assertStringContainsString($dto->toJsonLd()['offers']['price'], $response->getContent());
        $this->assertNotFalse(strpos($response->getContent(), '"price":"199.00"') !== false || strpos($response->getContent(), '"price": "199.00"') !== false || strpos($response->getContent(), $jsonLd) !== false);
    }

    public function test_feed_route_serves_xml(): void
    {
        $this->artisan('merchant:google-feed')->assertSuccessful();

        $this->get('/feeds/google-shopping.xml')
            ->assertOk()
            ->assertHeader('Content-Type', 'application/xml; charset=UTF-8');
    }

    public function test_legal_pages_are_public(): void
    {
        foreach ([
            '/contactos',
            '/ajuda/politica-de-envios',
            '/ajuda/politica-de-devolucoes',
            '/ajuda/politica-privacidade',
            '/ajuda/termos-e-condicoes',
        ] as $uri) {
            $this->get($uri)->assertOk();
        }

        $this->get('/ajuda/politica-de-envios')
            ->assertSee('Entrega em 1 a 3 dias úteis', false)
            ->assertSee('Envio gratuito para todo o Portugal', false);
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
