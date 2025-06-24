<?php

use App\Http\Controllers\Api\Webhook\AmoChatController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
Log::debug('full url' . url()->current());
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/amochat/{scope_id}/{ht?}', [AmoChatController::class, 'handle']);
