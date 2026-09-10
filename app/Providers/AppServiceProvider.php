<?php

namespace App\Providers;

use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer('*', function ($view) {
            $cart = app(\App\Services\CartService::class);
            $wishlist = app(\App\Services\WishlistService::class);

            $view->with([
                'cartData'      => $cart->toArray(),
                'cartCount'     => $cart->count(),
                'wishCount'     => $wishlist->count(),
                'wishIds'       => $wishlist->ids(),
                'authUser'      => auth()->user(),
            ]);

            if (! \Illuminate\Support\Facades\Schema::hasTable('categories')) {
                return;
            }
            $view->with('navCategories', \App\Models\Category::whereNull('parent_id')
                ->with('children')->orderBy('name')->get());
        });
        //
    }
}
