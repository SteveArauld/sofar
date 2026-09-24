<?php

namespace Tests\Feature;

use App\Domain\Merchant\GoogleFeedGenerator;
use App\Domain\Merchant\Support\Availability;
use App\DTO\Merchant\GoogleProductData;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class GoogleMerchantFeedTest extends TestCase
{
    use RefreshDatabase;

    /** @var array<string, ?string> path => original content (null if the file did not exist) */
    private array $liveFeedFileSnapshots = [];

    /**
     * The feed:build command writes to the real, live public/storage paths
     * served to Google (there is no injectable output path). Snapshot and
     * restore them around any test that invokes the command, so running the
     * test suite can never leave the production feed missing or altered.
     */
    private function snapshotLiveFeedFiles(): void
    {
        foreach ($this->liveFeedFilePaths() as $path) {
            $this->liveFeedFileSnapshots[$path] = is_file($path) ? file_get_contents($path) : null;
        }
    }

    private function restoreLiveFeedFiles(): void
    {
        foreach ($this->liveFeedFileSnapshots as $path => $original) {
            if ($original === null) {
                @unlink($path);
            } else {
                @file_put_contents($path, $original);
            }
        }
        $this->liveFeedFileSnapshots = [];
    }

    /** @return list<string> */
    private function liveFeedFilePaths(): array
    {
        return [
            public_path('feeds/google-merchant.xml'),
            public_path('dfpinteriores-gmc-conforme.xml'),
            storage_path('app/feeds/google-shopping.xml'),
            public_path('feeds/google-merchant.csv'),
            base_path('FEED-SELECTION.csv'),
        ];
    }

    private function item(array $overrides = []): GoogleProductData
    {
        static $id = 1;

        return new GoogleProductData(
            id: (string) ($overrides['id'] ?? $id++),
            title: $overrides['title'] ?? 'Sofá de teste',
            description: $overrides['description'] ?? 'Descrição de teste com mais de vinte caracteres.',
            link: $overrides['link'] ?? 'https://dfpinteriores.com/sofa-teste',
            imageLink: $overrides['imageLink'] ?? 'https://dfpinteriores.com/img/sofa.jpg',
            extraImages: $overrides['extraImages'] ?? [],
            availability: $overrides['availability'] ?? Availability::InStock,
            condition: 'new',
            priceValue: $overrides['priceValue'] ?? 199.0,
            salePriceValue: $overrides['salePriceValue'] ?? null,
            currency: 'EUR',
            brand: $overrides['brand'] ?? 'DFP Interiores',
            gtin: $overrides['gtin'] ?? null,
            mpn: $overrides['mpn'] ?? 'SKU1',
            identifierExists: true,
            googleProductCategory: 635,
            productType: 'Sofás',
            itemGroupId: null,
            color: null,
            size: null,
        );
    }

    private function assertWellFormed(string $xml): \DOMDocument
    {
        $previous = libxml_use_internal_errors(true);
        libxml_clear_errors();
        $doc = new \DOMDocument;
        $ok = $doc->loadXML($xml);
        $errors = libxml_get_errors();
        libxml_use_internal_errors($previous);

        $this->assertTrue($ok, 'XML failed to load');
        $this->assertSame([], $errors, 'XML has libxml errors: '.json_encode(array_map(fn ($e) => trim($e->message), $errors)));

        return $doc;
    }

    /** Reads a child tag of the first <item> element (title/link/description also exist at channel level). */
    private function firstItemChild(\DOMDocument $doc, string $tag): string
    {
        return $doc->getElementsByTagName('item')->item(0)->getElementsByTagName($tag)->item(0)->textContent;
    }

    public function test_feed_with_a_single_product_is_well_formed(): void
    {
        $generator = new GoogleFeedGenerator;
        $xml = $generator->xml([$this->item()]);

        $doc = $this->assertWellFormed($xml);
        $this->assertCount(1, $doc->getElementsByTagName('item'));
    }

    public function test_feed_with_multiple_products_is_well_formed(): void
    {
        $items = array_map(fn () => $this->item(), range(1, 25));

        $generator = new GoogleFeedGenerator;
        $xml = $generator->xml($items);

        $doc = $this->assertWellFormed($xml);
        $this->assertCount(25, $doc->getElementsByTagName('item'));
    }

    public function test_ampersand_in_title_is_escaped(): void
    {
        $generator = new GoogleFeedGenerator;
        $xml = $generator->xml([$this->item(['title' => 'Sofá & Cadeirão'])]);

        $doc = $this->assertWellFormed($xml);
        $this->assertSame('Sofá & Cadeirão', $this->firstItemChild($doc, 'title'));
    }

    public function test_ampersand_in_description_is_escaped(): void
    {
        $generator = new GoogleFeedGenerator;
        $xml = $generator->xml([$this->item(['description' => 'Feito em madeira & tecido de alta qualidade.'])]);

        $doc = $this->assertWellFormed($xml);
        $this->assertStringContainsString('&', $this->firstItemChild($doc, 'description'));
    }

    public function test_html_tags_in_description_do_not_break_the_xml(): void
    {
        $generator = new GoogleFeedGenerator;
        $xml = $generator->xml([$this->item(['description' => 'Sofá <strong>muito confortável</strong> para a sala.'])]);

        $doc = $this->assertWellFormed($xml);
        $this->assertStringContainsString('<strong>', $this->firstItemChild($doc, 'description'));
    }

    public function test_portuguese_accented_characters_are_preserved(): void
    {
        $title = 'Colchão árá âçã õú ê í ó pequeno-almoço';
        $generator = new GoogleFeedGenerator;
        $xml = $generator->xml([$this->item(['title' => $title])]);

        $doc = $this->assertWellFormed($xml);
        $this->assertSame($title, $this->firstItemChild($doc, 'title'));
    }

    public function test_spanish_and_french_accented_characters_are_preserved(): void
    {
        $title = 'Señor château très élégant niño';
        $generator = new GoogleFeedGenerator;
        $xml = $generator->xml([$this->item(['title' => $title])]);

        $doc = $this->assertWellFormed($xml);
        $this->assertSame($title, $this->firstItemChild($doc, 'title'));
    }

    public function test_apostrophes_are_preserved_and_valid(): void
    {
        $title = "L'armário d'angulo";
        $generator = new GoogleFeedGenerator;
        $xml = $generator->xml([$this->item(['title' => $title])]);

        $doc = $this->assertWellFormed($xml);
        $this->assertSame($title, $this->firstItemChild($doc, 'title'));
    }

    public function test_double_quotes_are_preserved_and_valid(): void
    {
        $title = 'Mesa "Premium" 120cm';
        $generator = new GoogleFeedGenerator;
        $xml = $generator->xml([$this->item(['title' => $title])]);

        $doc = $this->assertWellFormed($xml);
        $this->assertSame($title, $this->firstItemChild($doc, 'title'));
    }

    public function test_null_optional_fields_do_not_break_the_xml(): void
    {
        $generator = new GoogleFeedGenerator;
        $xml = $generator->xml([$this->item(['gtin' => null, 'salePriceValue' => null])]);

        $this->assertWellFormed($xml);
    }

    public function test_url_with_query_parameters_is_valid(): void
    {
        $generator = new GoogleFeedGenerator;
        $xml = $generator->xml([$this->item(['link' => 'https://dfpinteriores.com/sofa?ref=feed&utm_source=google'])]);

        $doc = $this->assertWellFormed($xml);
        $this->assertStringContainsString('utm_source=google', $this->firstItemChild($doc, 'link'));
    }

    public function test_product_with_image_includes_image_link(): void
    {
        $generator = new GoogleFeedGenerator;
        $xml = $generator->xml([$this->item(['imageLink' => 'https://dfpinteriores.com/img/sofa.jpg'])]);

        $doc = $this->assertWellFormed($xml);
        $this->assertSame(
            'https://dfpinteriores.com/img/sofa.jpg',
            $doc->getElementsByTagName('image_link')->item(0)->textContent
        );
    }

    public function test_product_without_image_still_produces_valid_xml(): void
    {
        $generator = new GoogleFeedGenerator;
        $xml = $generator->xml([$this->item(['imageLink' => ''])]);

        $this->assertWellFormed($xml);
    }

    public function test_feed_with_several_hundred_products_is_well_formed(): void
    {
        $items = array_map(fn () => $this->item(), range(1, 980));

        $generator = new GoogleFeedGenerator;
        $xml = $generator->xml($items);

        $doc = $this->assertWellFormed($xml);
        $this->assertCount(980, $doc->getElementsByTagName('item'));
    }

    public function test_invalid_control_character_is_rejected_by_validation_before_writing(): void
    {
        $generator = new GoogleFeedGenerator;

        // A raw control character (vertical tab, \x0B) is not permitted anywhere
        // in an XML 1.0 document per the spec, and htmlspecialchars() does not
        // strip or escape it — it must be caught by validation, not silently
        // written to disk.
        $items = [$this->item(['title' => "Sofá\x0Bpartido"])];

        $xml = $generator->xml($items);
        $error = $generator->validationError($xml);

        $this->assertNotNull($error, 'A raw control character should be detected as invalid XML');
    }

    public function test_write_produces_a_file_loadable_by_domdocument(): void
    {
        $generator = new GoogleFeedGenerator;
        $path = storage_path('app/feeds/test-google-shopping-'.uniqid().'.xml');

        $generator->write([$this->item(), $this->item(['title' => 'Cadeira & Mesa'])], $path);

        $this->assertFileExists($path);
        $doc = new \DOMDocument;
        $this->assertTrue($doc->load($path));
        $this->assertCount(2, $doc->getElementsByTagName('item'));

        unlink($path);
    }

    public function test_write_never_leaves_a_temp_file_behind(): void
    {
        $generator = new GoogleFeedGenerator;
        $dir = storage_path('app/feeds');
        $path = $dir.'/test-google-shopping-'.uniqid().'.xml';

        $generator->write([$this->item()], $path);

        $leftovers = array_filter(scandir($dir), fn ($f) => str_contains($f, '.tmp'));
        $this->assertSame([], array_values($leftovers));

        unlink($path);
    }

    public function test_write_throws_and_does_not_touch_existing_file_when_xml_would_be_invalid(): void
    {
        $path = storage_path('app/feeds/test-google-shopping-'.uniqid().'.xml');
        file_put_contents($path, '<rss><channel><title>previous good feed</title></channel></rss>');
        $originalContent = file_get_contents($path);

        $generator = new GoogleFeedGenerator;

        try {
            $generator->write([$this->item(['title' => "Sofá\x0Bpartido"])], $path);
            $this->fail('Expected a RuntimeException for invalid XML');
        } catch (\RuntimeException $e) {
            $this->assertStringContainsString('invalide', $e->getMessage());
        }

        $this->assertSame($originalContent, file_get_contents($path), 'The previously working file must not be overwritten by an invalid build');

        unlink($path);
    }

    /** @return list<string> absolute paths of the physical image files created, for cleanup */
    private function createEligibleProductsWithRealImages(int $count): array
    {
        $imagePaths = [];

        Product::factory()->count($count)->create()->each(function (Product $product) use (&$imagePaths) {
            $image = ProductImage::factory()->for($product)->create();
            $absolute = public_path($image->path);
            if (! is_dir(dirname($absolute))) {
                mkdir(dirname($absolute), 0775, true);
            }
            file_put_contents($absolute, 'fake-jpg-bytes');
            $imagePaths[] = $absolute;
        });

        return $imagePaths;
    }

    public function test_feed_build_command_generates_valid_xml_for_eligible_products(): void
    {
        $this->snapshotLiveFeedFiles();
        config(['feed.min_items_safety' => 1, 'feed.target_items' => 10]);

        $imagePaths = $this->createEligibleProductsWithRealImages(3);

        try {
            $exitCode = Artisan::call('feed:build');

            $this->assertSame(0, $exitCode, Artisan::output());

            $path = public_path('feeds/google-merchant.xml');
            $this->assertFileExists($path);

            $doc = new \DOMDocument;
            $this->assertTrue($doc->load($path));
            $this->assertCount(3, $doc->getElementsByTagName('item'));
        } finally {
            foreach ($imagePaths as $imagePath) {
                @unlink($imagePath);
            }
            $this->restoreLiveFeedFiles();
        }
    }

    public function test_feed_build_refuses_to_publish_when_below_safety_floor(): void
    {
        $this->snapshotLiveFeedFiles();
        config(['feed.min_items_safety' => 500, 'feed.target_items' => 980]);

        $publicPath = public_path('feeds/google-merchant.xml');
        if (! is_dir(dirname($publicPath))) {
            mkdir(dirname($publicPath), 0775, true);
        }
        file_put_contents($publicPath, '<rss><channel><title>previous good feed</title></channel></rss>');
        $before = file_get_contents($publicPath);

        try {
            // No eligible products created: every product will be excluded (no price/image/stock),
            // simulating the "everything got excluded" failure mode.
            Product::factory()->count(2)->create(['price' => 0, 'in_stock' => false]);

            $exitCode = Artisan::call('feed:build');

            $this->assertNotSame(0, $exitCode);
            $this->assertSame($before, file_get_contents($publicPath), 'Existing working feed must survive a failed/short build');
        } finally {
            $this->restoreLiveFeedFiles();
        }
    }
}
