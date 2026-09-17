<?php

namespace App\Providers;

use App\Domain\Merchant\MerchantPolicy;
use App\Models\Category;
use App\Services\CartService;
use App\Services\WishlistService;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
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
        if ($this->app->isProduction()) {
            URL::forceScheme('https');
        }

        View::composer('*', function ($view) {
            $cart = app(CartService::class);
            $wishlist = app(WishlistService::class);

            $view->with([
                'cartData' => $cart->toArray(),
                'cartCount' => $cart->count(),
                'wishCount' => $wishlist->count(),
                'wishIds' => $wishlist->ids(),
                'authUser' => auth()->user(),
                'organizationSchema' => MerchantPolicy::organizationSchema(),
                'merchantDelivery' => MerchantPolicy::deliveryCopy(),
            ]);
            if (! $view->offsetExists('canonical')) {
                $view->with('canonical', url()->current());
            }

            if (! Schema::hasTable('categories')) {
                return;
            }
            $view->with('navCategories', Category::whereNull('parent_id')
                ->with('children')->orderBy('name')->get());
        });
        //
    }
}
