<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /** @var list<string> */
    public const LOCALES = ['pt', 'it'];

    public const DEFAULT = 'pt';

    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->route('locale')
            ?? $request->segment(1)
            ?? $request->session()->get('locale', config('app.locale', self::DEFAULT));

        if (! in_array($locale, self::LOCALES, true)) {
            $locale = self::DEFAULT;
        }

        app()->setLocale($locale);
        $request->session()->put('locale', $locale);
        URL::defaults(['locale' => $locale]);

        // Empêche {locale} d'être injecté en 1er argument des contrôleurs
        // (Laravel passe les paramètres de route par position via array_values).
        if ($request->route()) {
            $request->route()->forgetParameter('locale');
        }

        return $next($request);
    }
}
