<?php

//setup weather configs.
/*
 * @TODO add additional weather providers (i.e. nws).
 */
return [
    'driver' => env('WEATHER_DRIVER', 'openweather'),
    'ttl' => env('WEATHER_TTL',3300), //55 min
    'openweather' => [
        'base_url' => env('OPEN_WEATHER_BASE_URL'),
        'api_key' => env('OPEN_WEATHER_API_KEY')
    ]
];