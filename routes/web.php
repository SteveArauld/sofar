<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\FeedController;
use App\Http\Controllers\Merchant\GoogleFeedController;
use App\Http\Controllers\StoreController;
use App\Http\Controllers\SupportController;
use App\Http\Controllers\WishlistController;
use App\Http\Middleware\SetLocale;
use App\Models\Product;
use Illuminate\Support\Facades\Route;

$locales = implode('|', SetLocale::LOCALES);

// Racine → locale par défaut
Route::get('/', fn () => redirect('/'.SetLocale::DEFAULT, 302));

// Assets / media (hors locale)
Route::get('/images/{path}', [StoreController::class, 'image'])->where('path', '.*')->name('image.proxy');
Route::get('/videos/{file}', [StoreController::class, 'video'])->where('file', '[A-Za-z0-9._-]+')->name('video');

/*
|--------------------------------------------------------------------------
| Routes préfixées /{locale}/…
|--------------------------------------------------------------------------
*/
Route::prefix('{locale}')
    ->where(['locale' => $locales])
    ->middleware(SetLocale::class)
    ->group(function () {
        Route::get('/', [StoreController::class, 'home'])->name('home');

        // Flux Google Merchant Center (par langue)
        Route::get('/feed/produtos.xml', [FeedController::class, 'xml'])->name('feed.xml');
        Route::get('/feed/produtos.xml/download', [FeedController::class, 'download'])->name('feed.download');
        Route::get('/feeds/google-shopping.xml', [GoogleFeedController::class, 'xml'])->name('feed.shopping');
        Route::get('/feeds/google-merchant.xml', [GoogleFeedController::class, 'xml'])->name('feed.gmc');
        Route::get('/dfpinteriores-gmc-conforme.xml', [GoogleFeedController::class, 'xml'])->name('gmc.conforme');
        Route::get('/admin/feed/health', [GoogleFeedController::class, 'health'])->name('feed.health');

        // Sitemap
        Route::get('/sitemap.xml', function () {
            $locale = app()->getLocale();
            $base = rtrim(config('feed.base_url'), '/').'/'.$locale;
            $static = ['', '/lojas', '/contactos', '/apoioaocliente', '/ajuda/termos-e-condicoes',
                '/ajuda/politica-privacidade', '/ajuda/politica-de-cookies', '/ajuda/politica-de-envios',
                '/ajuda/politica-de-devolucoes', '/ajuda/resolucao-alternativa-litigios'];

            return response()->stream(function () use ($base, $static) {
                $out = fopen('php://output', 'w');
                fwrite($out, '<?xml version="1.0" encoding="UTF-8"?>'."\n".'<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n");
                foreach ($static as $p) {
                    fwrite($out, '  <url><loc>'.htmlspecialchars($base.$p, ENT_XML1).'</loc></url>'."\n");
                }
                Product::query()->whereHas('images')->where('price', '>', 0)
                    ->orderBy('id')->chunk(500, function ($chunk) use ($out, $base) {
                        foreach ($chunk as $p) {
                            $seg = implode('/', array_map('rawurlencode', explode('/', ltrim((string) $p->slug, '/'))));
                            fwrite($out, '  <url><loc>'.htmlspecialchars($base.'/'.$seg, ENT_XML1).'</loc></url>'."\n");
                        }
                        flush();
                    });
                fwrite($out, '</urlset>'."\n");
                fclose($out);
            }, 200, ['Content-Type' => 'application/xml; charset=UTF-8']);
        })->name('sitemap');

        // Panier
        Route::post('/carrinho/add', [CartController::class, 'add'])->name('cart.add');
        Route::post('/carrinho/update', [CartController::class, 'update'])->name('cart.update');
        Route::post('/carrinho/remove', [CartController::class, 'remove'])->name('cart.remove');
        Route::get('/carrinho/json', [CartController::class, 'json'])->name('cart.json');

        // Checkout
        Route::get('/carrinho/checkout', [CheckoutController::class, 'show'])->name('checkout');
        Route::post('/carrinho/checkout', [CheckoutController::class, 'place'])->name('checkout.place');
        Route::get('/carrinho/checkout/sucesso/{ref}', [CheckoutController::class, 'success'])->name('checkout.success');

        // Favoris
        Route::post('/wishlist/toggle', [WishlistController::class, 'toggle'])->name('wishlist.toggle');
        Route::post('/wishlist/remove', [WishlistController::class, 'remove'])->name('wishlist.remove');
        Route::get('/wishlist', [WishlistController::class, 'page'])->name('wishlist');

        // Support / compte
        Route::get('/apoioaocliente', [SupportController::class, 'show'])->name('support');
        Route::post('/apoioaocliente', [SupportController::class, 'submit'])->name('support.submit');
        Route::post('/cliente/login', [AuthController::class, 'login'])->name('cliente.login');
        Route::post('/cliente/registo', [AuthController::class, 'register'])->name('cliente.registo');
        Route::post('/cliente/register', [AuthController::class, 'register']);
        Route::match(['get', 'post'], '/cliente/logout', [AuthController::class, 'logout'])->name('cliente.logout');

        // Recherche
        Route::get('/procura/sugestoes', [StoreController::class, 'searchSuggest'])->name('search.suggest');
        Route::match(['get', 'post'], '/procura', [StoreController::class, 'search'])->name('search');
        Route::match(['get', 'post'], '/procura/{term}', [StoreController::class, 'search'])->where('term', '.*')->name('search.term');
        Route::get('/catalogo/procura/{term}', function (string $term) {
            return redirect('/'.app()->getLocale().'/procura/'.$term, 301);
        })->where('term', '.*');

        // Pages
        Route::get('/lojas', [StoreController::class, 'page'])->defaults('key', 'lojas')->name('lojas');
        Route::get('/carrinho', [StoreController::class, 'page'])->defaults('key', 'carrinho')->name('carrinho');
        Route::get('/cliente', [StoreController::class, 'page'])->defaults('key', 'cliente')->name('cliente');
        Route::get('/ajuda/termos-e-condicoes', [StoreController::class, 'page'])->defaults('key', 'ajuda__termos-e-condicoes')->name('ajuda.termos');
        Route::get('/ajuda/politica-privacidade', [StoreController::class, 'page'])->defaults('key', 'ajuda__politica-privacidade')->name('ajuda.privacidade');
        Route::get('/ajuda/recrutamento', fn () => redirect()->route('ajuda.termos', ['locale' => app()->getLocale()], 301))->name('ajuda.recrutamento');
        Route::get('/ajuda/resolucao-alternativa-litigios', [StoreController::class, 'page'])->defaults('key', 'ajuda__resolucao-alternativa-litigios')->name('ajuda.ral');
        Route::get('/ajuda/politica-de-cookies', [StoreController::class, 'page'])->defaults('key', 'ajuda__politica-de-cookies')->name('ajuda.cookies');
        Route::get('/ajuda/politica-de-envios', [StoreController::class, 'page'])->defaults('key', 'ajuda__politica-de-envios')->name('ajuda.envios');
        Route::get('/ajuda/politica-de-devolucoes', [StoreController::class, 'page'])->defaults('key', 'ajuda__politica-de-devolucoes')->name('ajuda.devolucoes');
        Route::get('/contactos', [StoreController::class, 'page'])->defaults('key', 'contactos')->name('contactos');

        Route::match(['get', 'post'], '/descontos70', [StoreController::class, 'listing'])->defaults('key', 'descontos70')->name('descontos70');
        Route::match(['get', 'post'], '/artigosExclusivosOnline', [StoreController::class, 'listing'])->defaults('key', 'artigosExclusivosOnline')->name('exclusivos');

        Route::get('/_more/{key}', [StoreController::class, 'more'])->where('key', '.*')->name('listing.more');

        Route::get('/{slug}.html', function (string $slug) {
            return redirect('/'.app()->getLocale().'/'.$slug, 301);
        })->where('slug', '[A-Za-z0-9][A-Za-z0-9\-]*');

        Route::get('/catalogo/{slug}', function (string $slug) {
            return redirect('/'.app()->getLocale().'/'.$slug, 301);
        })->where('slug', '[A-Za-z0-9][A-Za-z0-9\-]*');

        Route::match(['get', 'post'], '/{slug}', [StoreController::class, 'show'])
            ->where('slug', '[A-Za-z0-9][A-Za-z0-9\-]*')
            ->name('show');
    });

/*
|--------------------------------------------------------------------------
| Compat : anciennes URLs sans préfixe → /pt/…
|--------------------------------------------------------------------------
*/
Route::get('/{any}', function (string $any) {
    if (in_array(explode('/', $any)[0], SetLocale::LOCALES, true)) {
        abort(404);
    }

    return redirect('/'.SetLocale::DEFAULT.'/'.$any, 301);
})->where('any', '^(?!pt(?:/|$)|it(?:/|$)|images(?:/|$)|videos(?:/|$)|assets(?:/|$)|media(?:/|$)|img(?:/|$)|storage(?:/|$)|feeds(?:/|$)|up$).+');
