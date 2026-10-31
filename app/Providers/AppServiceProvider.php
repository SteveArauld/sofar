<?php

namespace App\Providers;

use App\Domain\Merchant\MerchantPolicy;
use App\Http\Middleware\SetLocale;
use App\Models\Category;
use App\Models\Product;
use App\Services\CartService;
use App\Services\WishlistService;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        if ($this->app->isProduction()) {
            URL::forceScheme('https');
        }

        View::composer('*', function ($view) {
            $locale = app()->getLocale();
            if (! in_array($locale, SetLocale::LOCALES, true)) {
                $locale = SetLocale::DEFAULT;
            }
            URL::defaults(['locale' => $locale]);

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
                'currentLocale' => $locale,
                'localeUrls' => [
                    'pt' => switch_locale_url('pt'),
                    'it' => switch_locale_url('it'),
                ],
            ]);
            if (! $view->offsetExists('canonical')) {
                $view->with('canonical', url()->current());
            }

            if (! Schema::hasTable('categories')) {
                $view->with(['navCategories' => collect(), 'navMenu' => []]);

                return;
            }

            $navCategories = Category::whereNull('parent_id')
                ->with('children')
                ->orderBy(Product::translatedColumn('name'))
                ->get();

            $menuRoots = [
                ['slug' => 'mobiliario', 'label' => 'Mobiliário', 'icon' => 'mobiliario', 'iconId' => 'mobiliario'],
                ['slug' => 'sofas', 'label' => 'Sofás', 'icon' => 'sofa', 'iconId' => 'sofa'],
                ['slug' => 'decoracao', 'label' => 'Decoração', 'icon' => 'decoracao', 'iconId' => 'decoracao'],
                ['slug' => 'mobiliario-de-exterior', 'label' => 'Mobiliário de exterior', 'icon' => 'jardim', 'iconId' => 'jardim'],
                ['slug' => 'descanso', 'label' => 'Descanso', 'icon' => 'camas', 'iconId' => 'camas'],
            ];

            $bySlug = Category::query()
                ->with(['children.children'])
                ->whereIn('slug', array_column($menuRoots, 'slug'))
                ->get()
                ->keyBy('slug');

            $navMenu = [];
            foreach ($menuRoots as $spec) {
                $cat = $bySlug->get($spec['slug']);
                $navMenu[] = [
                    'slug' => $spec['slug'],
                    'label' => __($spec['label']),
                    'icon' => $spec['icon'],
                    'iconId' => $spec['iconId'],
                    'children' => $cat?->children ?? collect(),
                ];
            }

            $view->with([
                'navCategories' => $navCategories,
                'navMenu' => $navMenu,
            ]);
        });
    }
}
