<?php

namespace App\Http\Controllers\Merchant;

use App\Domain\Merchant\GoogleFeedGenerator;
use App\Http\Controllers\Controller;
use App\Http\Middleware\SetLocale;
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

        $path = $this->feedPath();
        abort_unless(is_file($path), 404);

        return response()->file($path, [
            'Content-Type' => 'application/xml; charset=UTF-8',
            'Cache-Control' => 'public, max-age=600',
        ]);
    }

    public function health(Request $request, GoogleFeedGenerator $generator): JsonResponse
    {
        if (! $this->tokenOk($request)) {
            abort(404);
        }

        $path = $this->feedPath();
        if (! is_file($path)) {
            return response()->json([
                'ok' => false,
                'error' => 'Aucun fichier de flux trouvé.',
                'locale' => app()->getLocale(),
            ], 503);
        }

        $xml = file_get_contents($path);
        $validationError = $xml === false ? 'Fichier illisible.' : $generator->validationError($xml);
        $itemCount = $xml !== false ? substr_count($xml, '<item>') : 0;

        return response()->json([
            'ok' => $validationError === null,
            'locale' => app()->getLocale(),
            'file' => str_replace(base_path().'/', '', $path),
            'valid_xml' => $validationError === null,
            'validation_error' => $validationError,
            'item_count' => $itemCount,
            'file_size_bytes' => filesize($path),
            'last_modified' => date(DATE_ATOM, filemtime($path)),
        ], $validationError === null ? 200 : 503);
    }

    private function feedPath(): string
    {
        $locale = app()->getLocale();
        if (! in_array($locale, SetLocale::LOCALES, true)) {
            $locale = SetLocale::DEFAULT;
        }

        $localized = public_path("feeds/google-merchant-{$locale}.xml");
        if (is_file($localized)) {
            return $localized;
        }

        // Fallback historique (PT)
        $legacy = public_path('feeds/google-merchant.xml');
        if (is_file($legacy)) {
            return $legacy;
        }

        return storage_path('app/feeds/google-shopping.xml');
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
