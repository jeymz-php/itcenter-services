<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // Laravel's built-in pagination view uses Tailwind-styled SVG icons
        // by default — this app has its own CSS system, not Tailwind, so
        // those icons rendered as giant unstyled black glyphs. This makes
        // every ->links() call across the whole app use our own plain,
        // correctly-sized Previous/Next + page-number view instead.
        Paginator::defaultView('partials.pagination');
        Paginator::defaultSimpleView('partials.pagination');
    }
}