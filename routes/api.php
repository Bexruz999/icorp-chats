<?php

use App\Http\Controllers\Api\Webhook\AmoChatController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
Log::debug('test' . json_encode([request()->all(), request()->headers->all()]));
Route::post('/amochat/{scope_id}', [AmoChatController::class, 'handle'])
    ->where('scope_id', '.*');

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

