<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\FeedController;
use App\Http\Controllers\StoreController;
use App\Http\Controllers\SupportController;
use App\Http\Controllers\WishlistController;
use Illuminate\Support\Facades\Route;

Route::get('/', [StoreController::class, 'home'])->name('home');

// --- Flux produits Google Merchant Center (XML) ---
Route::get('/feed/produtos.xml', [FeedController::class, 'xml'])->name('feed.xml');
Route::get('/feed/produtos.xml/download', [FeedController::class, 'download'])->name('feed.download');

// Flux GMC conforme (généré par `php artisan feed:build`) — URL publique stable
$serveFeed = function (string $file) {
    $path = public_path($file);
    abort_unless(is_file($path), 404);

    return response()->file($path, ['Content-Type' => 'application/xml; charset=UTF-8']);
};
Route::get('/feeds/google-merchant.xml', fn () => $serveFeed('feeds/google-merchant.xml'))->name('feed.gmc');
Route::get('/dfpinteriores-gmc-conforme.xml', fn () => $serveFeed('dfpinteriores-gmc-conforme.xml'))->name('gmc.conforme');

// Sitemap XML : pages institutionnelles + fiches produit publiées (avec image).
Route::get('/sitemap.xml', function () {
    $base = rtrim(config('feed.base_url'), '/');
    $static = ['', '/lojas', '/contactos', '/apoioaocliente', '/ajuda/termos-e-condicoes',
        '/ajuda/politica-privacidade', '/ajuda/politica-de-cookies', '/ajuda/politica-de-envios',
        '/ajuda/politica-de-devolucoes', '/ajuda/resolucao-alternativa-litigios', '/ajuda/recrutamento'];

    return response()->stream(function () use ($base, $static) {
        $out = fopen('php://output', 'w');
        fwrite($out, '<?xml version="1.0" encoding="UTF-8"?>'."\n".'<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n");
        foreach ($static as $p) {
            fwrite($out, '  <url><loc>'.htmlspecialchars($base.$p, ENT_XML1).'</loc></url>'."\n");
        }
        \App\Models\Product::query()->whereHas('images')->where('price', '>', 0)
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

// --- Panier (session) ---
Route::post('/carrinho/add', [CartController::class, 'add'])->name('cart.add');
Route::post('/carrinho/update', [CartController::class, 'update'])->name('cart.update');
Route::post('/carrinho/remove', [CartController::class, 'remove'])->name('cart.remove');
Route::get('/carrinho/json', [CartController::class, 'json'])->name('cart.json');

// --- Checkout ---
Route::get('/carrinho/checkout', [CheckoutController::class, 'show'])->name('checkout');
Route::post('/carrinho/checkout', [CheckoutController::class, 'place'])->name('checkout.place');
Route::get('/carrinho/checkout/sucesso/{ref}', [CheckoutController::class, 'success'])->name('checkout.success');

// --- Favoris (session) ---
Route::post('/wishlist/toggle', [WishlistController::class, 'toggle'])->name('wishlist.toggle');
Route::post('/wishlist/remove', [WishlistController::class, 'remove'])->name('wishlist.remove');
Route::get('/wishlist', [WishlistController::class, 'page'])->name('wishlist');

// --- Apoio ao cliente ---
Route::get('/apoioaocliente', [SupportController::class, 'show'])->name('support');
Route::post('/apoioaocliente', [SupportController::class, 'submit'])->name('support.submit');

// --- Compte client ---
Route::post('/cliente/login', [AuthController::class, 'login'])->name('cliente.login');
Route::post('/cliente/registo', [AuthController::class, 'register'])->name('cliente.registo');
Route::post('/cliente/register', [AuthController::class, 'register']);
Route::match(['get', 'post'], '/cliente/logout', [AuthController::class, 'logout'])->name('cliente.logout');

// Recherche : autocomplete (JSON) + page de résultats
Route::get('/procura/sugestoes', [StoreController::class, 'searchSuggest'])->name('search.suggest');
Route::match(['get', 'post'], '/procura', [StoreController::class, 'search'])->name('search');
Route::match(['get', 'post'], '/procura/{term}', [StoreController::class, 'search'])->where('term', '.*')->name('search.term');
Route::get('/catalogo/procura/{term}', fn (string $term) => redirect('/procura/'.$term, 301))->where('term', '.*');

// Proxy des images demandées par le JS officiel : /images/<taille>/products/<fichier>
Route::get('/images/{path}', [StoreController::class, 'image'])
    ->where('path', '.*')
    ->name('image.proxy');

// Vidéo servie via Laravel pour gérer les requêtes Range (206) — indispensable
// pour la lecture <video> sous `php artisan serve`.
Route::get('/videos/{file}', [StoreController::class, 'video'])
    ->where('file', '[A-Za-z0-9._-]+')
    ->name('video');

// Pages institutionnelles (vues Blade resources/views/pages/*)
Route::get('/lojas', [StoreController::class, 'page'])->defaults('key', 'lojas')->name('lojas');
Route::get('/carrinho', [StoreController::class, 'page'])->defaults('key', 'carrinho')->name('carrinho');
Route::get('/cliente', [StoreController::class, 'page'])->defaults('key', 'cliente')->name('cliente');
Route::get('/ajuda/termos-e-condicoes', [StoreController::class, 'page'])->defaults('key', 'ajuda__termos-e-condicoes')->name('ajuda.termos');
Route::get('/ajuda/politica-privacidade', [StoreController::class, 'page'])->defaults('key', 'ajuda__politica-privacidade')->name('ajuda.privacidade');
Route::get('/ajuda/recrutamento', [StoreController::class, 'page'])->defaults('key', 'ajuda__recrutamento')->name('ajuda.recrutamento');
Route::get('/ajuda/resolucao-alternativa-litigios', [StoreController::class, 'page'])->defaults('key', 'ajuda__resolucao-alternativa-litigios')->name('ajuda.ral');
Route::get('/ajuda/politica-de-cookies', [StoreController::class, 'page'])->defaults('key', 'ajuda__politica-de-cookies')->name('ajuda.cookies');
Route::get('/ajuda/politica-de-envios', [StoreController::class, 'page'])->defaults('key', 'ajuda__politica-de-envios')->name('ajuda.envios');
Route::get('/ajuda/politica-de-devolucoes', [StoreController::class, 'page'])->defaults('key', 'ajuda__politica-de-devolucoes')->name('ajuda.devolucoes');
Route::get('/contactos', [StoreController::class, 'page'])->defaults('key', 'contactos')->name('contactos');

// Pages de mise en avant : listes de produits (GET = page, POST = JSON filtres du JS officiel)
Route::match(['get', 'post'], '/descontos70', [StoreController::class, 'listing'])->defaults('key', 'descontos70')->name('descontos70');
Route::match(['get', 'post'], '/artigosExclusivosOnline', [StoreController::class, 'listing'])->defaults('key', 'artigosExclusivosOnline')->name('exclusivos');

// Chargement paginé (AJAX) pour toute liste : catégorie OU page de mise en avant
Route::get('/_more/{key}', [StoreController::class, 'more'])
    ->where('key', '.*')
    ->name('listing.more');

// Compat anciennes URLs du site : /<slug>.html et /catalogo/<slug> -> /<slug>
Route::get('/{slug}.html', fn (string $slug) => redirect("/$slug", 301))
    ->where('slug', '[A-Za-z0-9][A-Za-z0-9\-]*');
Route::get('/catalogo/{slug}', fn (string $slug) => redirect("/$slug", 301))
    ->where('slug', '[A-Za-z0-9][A-Za-z0-9\-]*');

// Schéma d'URL officiel : /{slug} = catégorie OU produit
// POST accepté : le JS officiel (ListaArtigos) poste les filtres sur l'URL de la catégorie.
Route::match(['get', 'post'], '/{slug}', [StoreController::class, 'show'])
    ->where('slug', '[A-Za-z0-9][A-Za-z0-9\-]*')
    ->name('show');
