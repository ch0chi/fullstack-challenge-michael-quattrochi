<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Services\WeatherService;
class WeatherUserController extends Controller
{
    public function getAllUsersWeatherSummary(Request $request)
    {
        try {
            $users = User::all()->select(['id','name','latitude','longitude'])->toArray();
            $weatherService = app(WeatherService::class);

            $weatherData = [];
            foreach ($users as $user) {
                if (isset($user['latitude']) && isset($user['longitude'])) {
                    $weather = $weatherService->getCurrentWeather($user['latitude'], $user['longitude']);
                    $weatherData[] = [
                        'user_id' => $user['id'],
                        'name' => $user['name'],
                        'latitude' => $user['latitude'],
                        'longitude' => $user['longitude'],
                        'weather' => $weather
                    ];
                } else {
                    $weatherData[] = [
                        'user_id' => $user['id'],
                        'name' => $user['name'],
                        'latitude' => null,
                        'longitude' => null,
                        'weather' => null,
                        'error' => 'No coordinates available'
                    ];
                }
            }

            return response()->json([
                'success' => true,
                'data' => $weatherData
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
