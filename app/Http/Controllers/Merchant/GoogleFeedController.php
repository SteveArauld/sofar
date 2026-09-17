<?php

namespace App\Http\Controllers\Merchant;

use App\Http\Controllers\Controller;
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

    private function tokenOk(Request $request): bool
    {
        $expected = (string) config('feed.feed_token', '');
        if ($expected === '') {
            return true;
        }

        return hash_equals($expected, (string) $request->query('token', ''));
    }
}
