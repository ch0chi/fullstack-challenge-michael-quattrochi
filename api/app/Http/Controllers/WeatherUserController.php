<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Services\WeatherService;
class WeatherUserController extends Controller
{
    /**
     * todo If I had more time, I would implement pagination for large user sets,
     *      batch the weather requests from the bus, and pole the job status until
     *      complete using socket.io or pusher.
     *
     * Get weather summary for all users with coordinates.
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAllUsersWeather(Request $request,WeatherService $weatherService): \Illuminate\Http\JsonResponse {
        try {
            $users = User::query()
                ->whereNotNull('latitude')
                ->whereNotNull('longitude')
                ->select(['id', 'name', 'latitude', 'longitude'])
                ->get()->toArray();

            $weatherData = $weatherService->getWeatherForUsers($users);

            return response()->json($weatherData);
        } catch (\Exception $e) {
            report($e);
            return response()->json("Error getting users weather data", 500);
        }
    }
}
