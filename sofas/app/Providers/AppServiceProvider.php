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
            if (! \Illuminate\Support\Facades\Schema::hasTable('categories')) {
                return;
            }
            $view->with('navCategories', \App\Models\Category::whereNull('parent_id')
                ->with('children')->orderBy('name')->get());
        });
        //
    }
}
