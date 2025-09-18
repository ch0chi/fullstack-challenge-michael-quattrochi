<?php

namespace App\Jobs;

use App\Models\User;
use App\Services\WeatherService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Job to refresh the weather cache for users with latitude and longitude set.
 */
class RefreshWeatherCache implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;



    public int $tries = 3;
    public int $timeout = 120;

    public function __construct() {
    }

    public function handle(WeatherService $weatherService): void
    {
        Log::info('Starting weather cache warm-up');

        User::query()
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->select(['id', 'name', 'latitude', 'longitude'])
            ->chunkById(100, function ($chunk) use ($weatherService) {
                $users = $chunk->map(fn ($user) => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'latitude' => (float) $user->latitude,
                    'longitude' => (float) $user->longitude,
                ])->all();

                $result = $weatherService->warmCacheForUsers($users);

                Log::info('Weather cache warm-up chunk result', [
                    'count' => count($users),
                    'successful' => $result['successful'] ?? 0,
                    'failed' => $result['failed'] ?? 0,
                ]);
            });

        Log::info('Finished weather cache warm-up');
    }
}
