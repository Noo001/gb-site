<?php

namespace App\Providers;

use App\Models\Price;
use App\Observers\PriceObserver;
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
        Price::observe(PriceObserver::class);
    }
}
