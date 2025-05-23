<?php

use App\Http\Controllers\Api\Webhook\AmoChatController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/amochat/{scope_id}', [AmoChatController::class, 'handle']);
Route::get('/messenger/get_media/{message_id}/{phone}', [AmoChatController::class, 'getMedia'])->name('tg.get-media');
