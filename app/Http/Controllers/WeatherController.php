<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\OpenWeatherService;
use App\Http\Resources\CurrentWeatherResource;
use App\Http\Resources\ForecastResource;
use Illuminate\Http\Request;
use Throwable;

class WeatherController extends Controller
{
    public function __construct(private OpenWeatherService $owm) {}

    public function current(Request $r)
    {
        try {
            $dto = $this->owm->current($r->query('city', 'Nairobi'));
            return new CurrentWeatherResource($dto);
        } catch (Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 503);
        }
    }

    public function forecast(Request $r)
    {
        try {
            $dto = $this->owm->forecast($r->query('city', 'Nairobi'));
            return new ForecastResource($dto);
        } catch (Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 503);
        }
    }
}