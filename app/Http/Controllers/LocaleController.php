<?php

namespace App\Http\Controllers;

use App\Http\Middleware\SetLocale;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LocaleController extends Controller
{
    public function switch(Request $request, string $locale): RedirectResponse
    {
        if (! in_array($locale, SetLocale::LOCALES, true)) {
            $locale = SetLocale::DEFAULT;
        }

        $request->session()->put('locale', $locale);
        app()->setLocale($locale);

        $target = $request->query('to');
        if (is_string($target) && $target !== '') {
            return redirect()->to($target);
        }

        return redirect()->to(switch_locale_url($locale));
    }
}
