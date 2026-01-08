<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FeederPingController;

Route::get('/', fn () => view('welcome'));

Route::get('/health', fn () => response()->json([
    'status' => 'ok',
    'app' => config('app.name'),
    'time' => now()->toDateTimeString(),
]));

Route::get('/feeder/ping', FeederPingController::class);
