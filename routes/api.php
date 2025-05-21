<?php

use App\Http\Controllers\WeatherController;
use Illuminate\Support\Facades\Route;

Route::get('/weather/current',  [WeatherController::class, 'current']);
Route::get('/weather/forecast', [WeatherController::class, 'forecast']);