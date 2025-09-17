<?php

namespace Tests\Unit;

use App\Exceptions\WeatherProviderException;
use Tests\TestCase;
use App\Services\WeatherService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;

class WeatherServiceTest extends TestCase
{
    use RefreshDatabase;
    /**
     * @var WeatherService
     */
    protected WeatherService $weatherService;

    public function setUp(): void
    {
        parent::setUp();
        $this->weatherService = $this->app->make(WeatherService::class);
    }

    /**
     * Test fetching weather data from the API.
     *
     * @return void
     */
    public function testCanGetWeatherDataFromApi() {
        try {
            $lat = 39.750959;
            $lon = -105.064573;
            $data = $this->weatherService->fetchCurrentWeatherFromApi($lat,$lon);

            $this->assertIsArray($data);
            $this->assertNotEmpty($data);

        } catch(WeatherProviderException $e) {
            $this->fail('Exception thrown: ' . $e->getMessage());
        }
    }

    /**
     * @todo If I had more time, I would mock the HTTP client to simulate API failures.
     * Test that WeatherProviderException is thrown for invalid coordinates.
     *
     * @return void
     */
    public function testWeatherProviderExceptionIsThrownForInvalidCoordinates() {
        $this->expectException(WeatherProviderException::class);
        $lat = 999; // Invalid latitude
        $lon = 999; // Invalid longitude
        $this->weatherService->fetchCurrentWeatherFromApi($lat, $lon);
    }

    /**
     * Test warming the cache for users.
     *
     * @return void
     */
    public function testCanWarmCacheForUsers():void {
        // Create test users
        User::factory()->create([
            'latitude' => 40.7128,
            'longitude' => -74.0060
        ]);

        User::factory()->create([
            'latitude' => 34.0522,
            'longitude' => -118.2437
        ]);

        $users = User::all()->select(['id','name','latitude','longitude'])->toArray();
        //todo  I'd normally chunk this to avoid memory issues and mock the HTTP client.
        //      Using a DTO would also be preferable to an array.
//        foreach(User::all() as $user) {
//            $users[] = [
//                'id' => $user->id,
//                'name' => $user->name,
//                'latitude' => $user->latitude,
//                'longitude' => $user->longitude
//            ];
//        }

        $result = $this->weatherService->warmCacheForUsers($users);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('total', $result);
        $this->assertArrayHasKey('successful', $result);
        $this->assertArrayHasKey('failed', $result);
        $this->assertArrayHasKey('errors', $result);
        $this->assertEquals(2, $result['total']);
        $this->assertEquals(2, $result['successful']);
        $this->assertEquals(0, $result['failed']);
    }

    /**
     * Test fetching weather data uses cache when data is fresh.
     * @return void
     */
    public function testCanGetWeatherIfCacheIsStale() {
        // Clear cache to ensure fresh state
        Cache::flush();

        // Create a test user
        $user = User::factory()->create([
            'latitude' => 40.7128,
            'longitude' => -74.0060
        ]);

        $timeStampKey = "weather:current:timestamp:{$user->latitude}:{$user->longitude}";

        $wx1 = $this->weatherService->getCurrentWeather($user->latitude, $user->longitude);
        $this->assertIsArray($wx1);
        $this->assertArrayHasKey('temperature', $wx1);

        //fetch the timestamp from cache
        $timeStampWx1 = Cache::get($timeStampKey);
        $this->assertNotNull($timeStampWx1);

        // Simulate cache expiration by manually clearing the cache
        Cache::flush();
        usleep(1000000); //1 second to ensure timestamp difference

        // Second call should fetch from API again since cache is cleared
        $wx2 = $this->weatherService->getCurrentWeather($user->latitude, $user->longitude);
        $this->assertIsArray($wx2);

        $timeStampWx2 = Cache::get($timeStampKey);
        $this->assertNotNull($timeStampWx2);

        // The two timestamps should be different, indicating a fresh fetch
        $this->assertNotEquals($timeStampWx1, $timeStampWx2);

    }

}
