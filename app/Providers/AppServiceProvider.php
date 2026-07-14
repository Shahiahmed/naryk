<?php

namespace App\Providers;

use App\View\Composers\SiteComposer;
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
        // Every site view, not just the layout: a child view that extends it
        // still needs the settings, and tests read them off the root view.
        // `errors.*` covers the 404 page, which extends the same layout.
        View::composer(['site.*', 'errors.*'], SiteComposer::class);
    }
}
