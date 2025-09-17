<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Services\WeatherService;
class WeatherUserController extends Controller
{
    /**
     * todo If I had more time, I would implement pagination for large user sets
     *      It's not ideal to ever fetch an entire table at once. It's a massive scalability issue.
     *      I'm also not doing any validation on the coordinates, assuming they are valid if present.
     *
     * Get weather summary for all users with coordinates.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAllUsersWeather(Request $request,WeatherService $weatherService): \Illuminate\Http\JsonResponse {
        try {
            $users = User::all()->select(['id','name','latitude','longitude'])->toArray();

            $weatherData = [];
            foreach ($users as $user) {
                $weather = $weatherService->getCurrentWeather($user['latitude'], $user['longitude']);
                $weatherData[] = [
                    'user' => $user,
                    'weather' => $weather
                ];
            }

            return response()->json($weatherData);
        } catch (\Exception $e) {
            report($e);
            return response()->json("Error getting users weather data", 500);
        }
    }
}
