<?php

namespace App\Providers;

use App\Contracts\PaymentProviderInterface;
use App\Services\Providers\CoinbaseProvider;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
