<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\WeatherService;
class WeatherServiceProvider extends ServiceProvider
{
    /**
     * @TODO
     *      If I had more time, I would create services for each Weather Service (i.e. Openweather, NWS)).
     *      I'd also return the appropriate service based on the driver set in the config.
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(WeatherService::class, function ($app) {
            $driver = config('services.weather.driver', 'openweather');
            // For now, we only have one driver, so we return the same service.
            // In the future, you could have a switch statement here to return different services
            // or use a factory pattern.
            return new WeatherService(config('weather.openweather.base_url'), config('weather.openweather.api_key'));
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
