<?php

use App\Http\Middleware\SetLocale;

if (! function_exists('locale_url')) {
    /**
     * URL préfixée par la locale courante (ex. /pt/lojas, /it/sofas).
     */
    function locale_url(string $path = '', ?string $locale = null): string
    {
        $locale = $locale ?? app()->getLocale();
        if (! in_array($locale, SetLocale::LOCALES, true)) {
            $locale = SetLocale::DEFAULT;
        }

        $path = trim($path, '/');

        return $path === ''
            ? url('/'.$locale)
            : url('/'.$locale.'/'.$path);
    }
}

if (! function_exists('switch_locale_url')) {
    /**
     * Même chemin que la requête courante, dans une autre locale.
     */
    function switch_locale_url(string $locale): string
    {
        if (! in_array($locale, SetLocale::LOCALES, true)) {
            $locale = SetLocale::DEFAULT;
        }

        $path = trim(request()->path(), '/');
        $segments = $path === '' ? [] : explode('/', $path);

        if ($segments !== [] && in_array($segments[0], SetLocale::LOCALES, true)) {
            $segments[0] = $locale;
        } else {
            array_unshift($segments, $locale);
        }

        return url('/'.implode('/', $segments));
    }
}
