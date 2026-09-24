<?php

namespace App\Http\Controllers\Merchant;

use App\Domain\Merchant\GoogleFeedGenerator;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;

class GoogleFeedController extends Controller
{
    public function xml(Request $request): BinaryFileResponse|Response
    {
        if (! $this->tokenOk($request)) {
            abort(404);
        }

        $path = public_path('feeds/google-merchant.xml');
        if (! is_file($path)) {
            $path = storage_path('app/feeds/google-shopping.xml');
        }
        abort_unless(is_file($path), 404);

        return response()->file($path, [
            'Content-Type' => 'application/xml; charset=UTF-8',
            'Cache-Control' => 'public, max-age=600',
        ]);
    }

    /**
     * Internal diagnostic endpoint: is the served feed present and valid,
     * how many items does it contain, when was it last built. Gated behind
     * the same MERCHANT_FEED_TOKEN as the feed itself (set it in .env to
     * restrict access — with no token configured this route, like the feed
     * route, is unauthenticated).
     */
    public function health(Request $request, GoogleFeedGenerator $generator): JsonResponse
    {
        if (! $this->tokenOk($request)) {
            abort(404);
        }

        $path = public_path('feeds/google-merchant.xml');
        $usedFallback = false;
        if (! is_file($path)) {
            $path = storage_path('app/feeds/google-shopping.xml');
            $usedFallback = true;
        }

        if (! is_file($path)) {
            return response()->json([
                'ok' => false,
                'error' => 'Aucun fichier de flux trouvé.',
            ], 503);
        }

        $xml = file_get_contents($path);
        $validationError = $xml === false ? 'Fichier illisible.' : $generator->validationError($xml);
        $itemCount = $xml !== false ? substr_count($xml, '<item>') : 0;

        return response()->json([
            'ok' => $validationError === null,
            'file' => $usedFallback ? 'storage/app/feeds/google-shopping.xml (fallback)' : 'public/feeds/google-merchant.xml',
            'valid_xml' => $validationError === null,
            'validation_error' => $validationError,
            'item_count' => $itemCount,
            'file_size_bytes' => filesize($path),
            'last_modified' => date(DATE_ATOM, filemtime($path)),
        ], $validationError === null ? 200 : 503);
    }

    private function tokenOk(Request $request): bool
    {
        $expected = (string) config('feed.feed_token', '');
        if ($expected === '') {
            return true;
        }

        return hash_equals($expected, (string) $request->query('token', ''));
    }
}
