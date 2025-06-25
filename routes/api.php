<?php

use App\Http\Controllers\Api\Webhook\AmoChatController;
use App\Models\AmoConnection;
use App\Models\UserMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
Route::post('/{scope_id}', [AmoChatController::class, 'handle'])
    ->where('scope_id', '.*');

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// ///////////////////////////////////////////////

$request = request();
$telegram = new \App\Services\TelegramService();

Log::debug('test' . json_encode([request()->all(), request()->headers->all()]));

if (empty($request->header('X-Signature'))) {
    return response()->json(['message' => 'Invalid signature'], 403);
}

$message = $request->post('message');
$amoConnection = AmoConnection::where('amojo_id', $message['sender']['id'])->firstOrFail();
$sender = $amoConnection->user()->whereNotNull('telegram_id')->firstOrFail();

$receiver = Str::after($message['receiver']['client_id'], 'user-');

Log::debug('Receiver: ' . json_encode(['receiver' => $receiver, 'sender' => $sender]));

if ($message['message']['type'] == 'text') {

    $sendMessage = $telegram->sendMessage(
        phone: $sender->phone, peerId: $receiver, message: $message['message']['text']
    );

} elseif (in_array($message['message']['type'], ['file', 'video', 'picture', 'audio'])) {

    $sendMessage = $telegram->sendMedia(
        phone: $sender->phone,
        peerId: $receiver,
        type: $telegram->getMediaTypeForMadelineProto($message['message']['file_name']),
        path: $message['message']['media'],
        fileName: $message['message']['file_name'], message: $message['message']['text']
    );

} else {
    return response()->json(['status' => 'error', 'message' => 'Unsupported message type'], 400);
}

Log::debug('sendMessage: ' . json_encode($sendMessage));

if ($sendMessage['success']) {

    UserMessage::create([
        'user_id' => $sender->id,
        'chat_id' => $receiver,
        'message_id' => $sendMessage['result']['id'],
    ]);

    Cache::put(key: "amocrm_$receiver-{$sendMessage['result']['id']}", value: $sender->name, ttl: 86400);

    return response()->json(['message' => 'Webhook received successfully']);
}

return response()->json(['status' => 'error'], 500);
