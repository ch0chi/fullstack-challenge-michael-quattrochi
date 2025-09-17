<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\WeatherService;
class WeatherServiceProvider extends ServiceProvider
{
    /**
     * @TODO If I had more time, I would make separate weather providers for each Weather Service (i.e. Openweather, NWS))
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(WeatherService::class, function ($app) {
            return new WeatherService();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
