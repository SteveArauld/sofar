<?php

namespace App\Console\Commands\Merchant;

use App\Domain\Merchant\GoogleFeedValidator;
use App\Domain\Merchant\GoogleProductMapper;
use App\Domain\Merchant\Support\Gtin;
use App\Domain\Merchant\Support\PriceFormatter;
use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use SimpleXMLElement;

class AuditGoogleFeedCommand extends Command
{
    protected $signature = 'merchant:google-feed-audit
        {--limit=20 : Sample size}
        {--all : Audit every item in the generated XML}
        {--http : Fetch landing pages over HTTP}';

    protected $description = 'Compare DB mapper vs XML vs PDP (prix / stock / GTIN)';

    public function handle(GoogleProductMapper $mapper, GoogleFeedValidator $validator): int
    {
        $path = storage_path('app/feeds/google-shopping.xml');
        if (! is_file($path)) {
            $path = public_path('feeds/google-merchant.xml');
        }
        if (! is_file($path)) {
            $this->error('Flux introuvable. Lancez php artisan merchant:google-feed');

            return self::FAILURE;
        }

        $xml = simplexml_load_file($path);
        if ($xml === false) {
            $this->error('XML illisible.');

            return self::FAILURE;
        }
        $xml->registerXPathNamespace('g', 'http://base.google.com/ns/1.0');

        $items = $xml->channel->item ?? [];
        $total = count($items);
        $limit = $this->option('all') ? $total : max(1, (int) $this->option('limit'));
        $sample = [];
        $i = 0;
        foreach ($items as $item) {
            $sample[] = $item;
            $i++;
            if ($i >= $limit) {
                break;
            }
        }

        $failures = 0;
        $this->info("Audit de {$i} / {$total} items");

        foreach ($sample as $node) {
            $id = (string) $this->g($node, 'id');
            $product = Product::with(['images', 'specs'])->find($id);
            if (! $product) {
                $this->error("id={$id} absent de la DB");
                $failures++;

                continue;
            }

            $dto = $mapper->mapForLanding($product);
            $xmlPrice = (string) $this->g($node, 'price');
            $xmlSale = (string) $this->g($node, 'sale_price');
            $xmlAvail = (string) $this->g($node, 'availability');
            $xmlGtin = (string) $this->g($node, 'gtin');
            $xmlLink = (string) $node->link;

            $errors = $validator->validate($dto);

            if ($dto->formattedPrice() !== $xmlPrice) {
                $errors[] = "price XML={$xmlPrice} mapper={$dto->formattedPrice()}";
            }
            $mappedSale = $dto->formattedSalePrice() ?? '';
            if ($mappedSale !== $xmlSale) {
                $errors[] = "sale_price XML={$xmlSale} mapper={$mappedSale}";
            }
            if ($dto->availability->value !== $xmlAvail) {
                $errors[] = "availability XML={$xmlAvail} mapper={$dto->availability->value}";
            }
            if ($xmlGtin !== '' && ! Gtin::isValid($xmlGtin)) {
                $errors[] = "GTIN XML invalide {$xmlGtin}";
            }
            if ($dto->link !== $xmlLink) {
                $errors[] = "link XML={$xmlLink} mapper={$dto->link}";
            }

            if ($this->option('http')) {
                $errors = array_merge($errors, $this->auditLanding($xmlLink, $dto->offerPriceValue(), $dto->availability->schemaUrl()));
            }

            if ($errors) {
                $failures++;
                $this->error("id={$id} ".$product->slug);
                foreach ($errors as $err) {
                    $this->line('  - '.$err);
                }
            }
        }

        if ($failures > 0) {
            $this->error("{$failures} item(s) en écart.");

            return self::FAILURE;
        }

        $this->info('Aucun écart prix / stock / GTIN.');

        return self::SUCCESS;
    }

    private function g(SimpleXMLElement $node, string $name): SimpleXMLElement|string
    {
        $children = $node->children('http://base.google.com/ns/1.0');

        return $children->{$name} ?? '';
    }

    /**
     * @return list<string>
     */
    private function auditLanding(string $url, float $expectedPrice, string $expectedAvailability): array
    {
        $errors = [];
        $response = Http::timeout(20)->withHeaders([
            'User-Agent' => 'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)',
        ])->get($url);

        if (! $response->ok()) {
            return ["landing HTTP {$response->status()} {$url}"];
        }

        $html = $response->body();
        if (! preg_match('/<script type="application\/ld\+json">(.*?)<\/script>/s', $html, $m)) {
            return ['JSON-LD absent de la PDP'];
        }

        $json = json_decode(html_entity_decode($m[1], ENT_QUOTES | ENT_HTML5, 'UTF-8'), true);
        if (! is_array($json)) {
            return ['JSON-LD illisible'];
        }

        $offer = $json['offers'] ?? [];
        $ldPrice = isset($offer['price']) ? (float) $offer['price'] : null;
        if ($ldPrice === null || abs($ldPrice - $expectedPrice) > 0.001) {
            $errors[] = 'JSON-LD price='.($offer['price'] ?? '∅').' attendu='.PriceFormatter::amount($expectedPrice);
        }
        if (($offer['availability'] ?? '') !== $expectedAvailability) {
            $errors[] = 'JSON-LD availability='.($offer['availability'] ?? '∅');
        }

        return $errors;
    }
}
