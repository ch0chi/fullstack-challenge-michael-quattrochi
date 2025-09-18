<?php

/*
 * Keeping defaults for cache ttl and max age to the same value for simplicity.
 * In a production system, these might differ based on requirements.
 * @TODO add additional weather providers (i.e. nws).
 */
return [
    'driver' => env('WEATHER_DRIVER', 'openweather'),
    'ttl' => env('WEATHER_TTL',3300), //55 min
    'max_cache_age'=> env('WEATHER_MAX_CACHE_AGE', 3300), //55 min
    'openweather' => [
        'base_url' => env('OPEN_WEATHER_BASE_URL'),
        'api_key' => env('OPEN_WEATHER_API_KEY')
    ]
];