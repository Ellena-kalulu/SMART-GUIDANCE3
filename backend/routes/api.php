<?php

use App\Http\Controllers\UssdController;
use Illuminate\Support\Facades\Route;

Route::get('/health', fn () => response()->json([
    'status' => 'ok',
    'app' => config('app.name'),
    'time' => now()->toIso8601String(),
]))->name('api.health');

Route::post('/ussd/callback', [UssdController::class, 'callback'])->name('ussd.callback');
