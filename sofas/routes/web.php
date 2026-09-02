<?php

use App\Http\Controllers\StoreController;
use Illuminate\Support\Facades\Route;

Route::get('/', [StoreController::class, 'home'])->name('home');

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

// Pages institutionnelles (gabarits officiels : resources/mirror/pages/*)
Route::get('/lojas', [StoreController::class, 'page'])->defaults('key', 'lojas')->name('lojas');
Route::get('/carrinho', [StoreController::class, 'page'])->defaults('key', 'carrinho')->name('carrinho');
Route::get('/wishlist', [StoreController::class, 'page'])->defaults('key', 'wishlist')->name('wishlist');
Route::get('/cliente', [StoreController::class, 'page'])->defaults('key', 'cliente')->name('cliente');
Route::get('/ajuda/termos-e-condicoes', [StoreController::class, 'page'])->defaults('key', 'ajuda__termos-e-condicoes')->name('ajuda.termos');
Route::get('/ajuda/politica-privacidade', [StoreController::class, 'page'])->defaults('key', 'ajuda__politica-privacidade')->name('ajuda.privacidade');
Route::get('/ajuda/recrutamento', [StoreController::class, 'page'])->defaults('key', 'ajuda__recrutamento')->name('ajuda.recrutamento');
Route::get('/ajuda/resolucao-alternativa-litigios', [StoreController::class, 'page'])->defaults('key', 'ajuda__resolucao-alternativa-litigios')->name('ajuda.ral');

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
