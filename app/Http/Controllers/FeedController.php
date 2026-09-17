<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Merchant\GoogleFeedController;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * Legacy feed URLs — same XML as /feeds/google-merchant.xml (GoogleProductMapper).
 */
class FeedController extends Controller
{
    public function xml(Request $request): BinaryFileResponse|Response
    {
        return app(GoogleFeedController::class)->xml($request);
    }

    public function download(Request $request): BinaryFileResponse|Response
    {
        $response = $this->xml($request);
        if ($response instanceof BinaryFileResponse) {
            $response->headers->set(
                'Content-Disposition',
                'attachment; filename="google-merchant-'.now()->format('Y-m-d').'.xml"'
            );
        }

        return $response;
    }
}
