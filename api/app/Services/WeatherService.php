<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use App\Exceptions\WeatherProviderException;
class WeatherService
{
    private string $apiKey;
    private string $baseUrl;
    private int $cacheTtl; // 30 minutes in seconds (shorter TTL for freshness)
    private int $maxCacheAge = 3300; // 55min  maximum age before considering stale

    public function __construct(?string $baseUrl, ?string $apiKey)
    {
        $this->baseUrl = config('weather.openweather.base_url');
        $this->apiKey = config('weather.openweather.api_key');
        $this->cacheTtl = config('weather.ttl');
    }

    /**
     * Get current weather for a specific location
     *
     * @param float $latitude
     * @param float $longitude
     * @param bool $forceRefresh Force refresh even if cache exists
     * @return array|null
     */
    public function getCurrentWeather(float $latitude, float $longitude, bool $forceRefresh = false): ?array
    {
        $cacheKey = $this->makeCacheKey('weather:current', $latitude, $longitude);
        $timestampKey = $this->makeCacheKey('weather:current:timestamp', $latitude, $longitude);

        $cached = Cache::get($cacheKey);
        $stale = $this->isCacheStale($timestampKey);

        $needFetch = $forceRefresh || !$cached || $stale;

        if (!$needFetch) {
            return $cached;
        }

        try {
            $currWeather = $this->fetchCurrentWeatherFromApi($latitude, $longitude);
            $currWeather = $this->mapCurrentWeather($currWeather);

            Cache::put($cacheKey, $currWeather, $this->cacheTtl);
            Cache::put($timestampKey, now()->timestamp, $this->cacheTtl);

            return $currWeather;
        } catch (WeatherProviderException $e) {
            report($e);

            return null; // No data available
        }
    }

    /**
     * Fetch current weather from API
     *
     * @param float $latitude
     * @param float $longitude
     *
     * @return array|null
     * @throws \App\Exceptions\WeatherProviderException
     */
    public function fetchCurrentWeatherFromApi(float $latitude, float $longitude): ?array
    {
        try {

            $response = Http::timeout(5)->get($this->baseUrl . '/weather', [
                'lat' => $latitude,
                'lon' => $longitude,
                'appid' => $this->apiKey,
                'units' => 'imperial'
            ]);

            if ($response->successful()) {
                return $response->json();
            }

            throw new WeatherProviderException(
                'Failed to fetch current weather: ' . $response->body(),
                $response->status(),
                ['coordinates' => [$latitude,$longitude]]
            );


        } catch (\Exception $e) {
            throw new WeatherProviderException(
                'Failed to fetch current weather: ' . $e->getMessage(),
                $e->getCode(),
                ['coordinates' => [$latitude,$longitude]]
            );
        }
    }

    /**
     * Check if cache is stale (older than maxCacheAge)
     *
     * @param string $timestampKey
     * @return bool
     */
    private function isCacheStale(string $timestampKey): bool
    {
        $timestamp = Cache::get($timestampKey);
        if (!$timestamp) {
            return true;//No timestamp assumes stale
        }

        $cacheAge = now()->timestamp - $timestamp;
        return $cacheAge > $this->maxCacheAge;
    }

    /**
     * @todo
     *      If I had more time, I would batch, cache, and implement async calls
     *      via something like guzzle promises. If not, the array could (would)
     *      eventually get too large and be the source of a memory leak.
     *      I would also implement rate limiting to avoid hitting API limits.
     *
     * Get weather data for multiple users. Due to time constraints, we are assuming
     * the array of users is not massive.
     *
     * @param array $users Array of users with latitude and longitude
     * @return array
     */
    public function getWeatherForUsers(array $users): array
    {
        $weatherData = [];

        foreach ($users as $user) {
            $timeStampKey = $this->makeCacheKey('weather:current:timestamp', $user['latitude'], $user['longitude']);

            $weather = $this->getCurrentWeather($user['latitude'], $user['longitude']);

            $lastUpdated = Cache::get($timeStampKey);
            if($lastUpdated !== null) {
                $lastUpdated = now()->createFromTimestamp($lastUpdated)->toISOString();
            }

            $weatherData[] = [
                'user' => $user,
                'weather' => $weather ?? null,
                'last_updated' => $lastUpdated ?? null
            ];
        }

        return $weatherData;
    }

    /**
     * todo This would be a DTO if I had more time.
     * Format current weather data for API response
     *
     * @param array $weatherData
     * @return array
     */
    private function mapCurrentWeather(array $weatherData): array
    {
        return [
            'temperature' => $weatherData['main']['temp'] ?? null,
            'feels_like' => $weatherData['main']['feels_like'] ?? null,
            'humidity' => $weatherData['main']['humidity'] ?? null,
            'pressure' => $weatherData['main']['pressure'] ?? null,
            'conditions' => $weatherData['weather'][0]['main'] ?? null,
            'icon' => $weatherData['weather'][0]['icon'] ?? null,//probably won't use this on the frontend since, but who knows
            'wind_speed' => $weatherData['wind']['speed'] ?? null,
            'wind_direction' => $weatherData['wind']['deg'] ?? null,
            'wind_gust' => $weatherData['wind']['gust'] ?? null,
            'wind_degree' => $weatherData['wind']['deg'] ?? null,
            'visibility' => $weatherData['visibility'] ?? null,
            'cloudiness' => $weatherData['clouds']['all'] ?? null,
        ];
    }

    /**
     * Create a unique cache key based on prefix and coordinates
     *
     * @param string $prefix
     * @param float $latitude
     * @param float $longitude
     * @return string
     */
    public function makeCacheKey(string $prefix, float $latitude, float $longitude): string
    {
        return "{$prefix}:{$latitude}:{$longitude}";
    }

    /**
     * @todo If I had more time, I'd make this into it's own caching service-
     *      including rate limiting, queueing, and async calls.
     * Warm the cache for all users by pre-fetching weather data
     *
     * @param array $users Array of users with latitude and longitude
     * @return array Results of cache warming
     */
    public function warmCacheForUsers(array $users): array
    {
        $results = [
            'total' => count($users),
            'successful' => 0,
            'failed' => 0,
            'errors' => []
        ];

        foreach ($users as $user) {
            try {

                $currentWeather = $this->getCurrentWeather(
                    $user['latitude'],
                    $user['longitude'],
                    true
                );

                if ($currentWeather) {
                    $results['successful']++;
                } else {
                    $results['failed']++;
                    $results['errors'][] = "Failed to fetch weather for user {$user['id']}";
                }

                //Makeshift rate limiting due to time constraints
                usleep(100000); //0.1s

            } catch (\Exception $e) {
                $results['failed']++;
                $results['errors'][] = "Error for user {$user['id']}: " . $e->getMessage();
            }
        }

        return $results;
    }

    /**
     * Get cache statistics for monitoring.
     *
     * @return array
     */
    public function getCacheStats(): array
    {
        return [
            'cache_ttl' => $this->cacheTtl,
            'max_cache_age' => $this->maxCacheAge,
            'cache_driver' => config('cache.default'),
            'timestamp' => now()->toISOString()
        ];
    }
}
