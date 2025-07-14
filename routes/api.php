<?php

use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\Webhook\AmoChatController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/amochat/{scope_id}', [AmoChatController::class, 'handle']);

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::get('/user', function (Request $request) {
    return $request->user();
});
