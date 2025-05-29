<?php

namespace App\Http\Controllers\Api\Webhook;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserMessage;
use App\Services\TelegramService;
use Cache;
use Illuminate\Http\Request;
use Str;

class AmoChatController extends Controller
{
    public function handle(Request $request, TelegramService $telegram)
    {
        if (empty($request->header('X-Signature'))) {
            return response()->json(['message' => 'Invalid signature'], 403);
        }

        $message = $request->post('message');
        $sender = User::where('amojo_id', $message['sender']['id'])
            ->whereNotNull('telegram_id')
            ->firstOrFail();

        $receiver = Str::after($message['receiver']['client_id'], 'user-');

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
            file_put_contents('cache3.txt', json_encode($sendMessage, JSON_PRETTY_PRINT));

        } else {
            return response()->json(['status' => 'error', 'message' => 'Unsupported message type'], 400);
        }

        if ($sendMessage['success']) {
            UserMessage::create([
                'user_id' => $sender->id,
                'chat_id' => $receiver,
                'message_id' => $sendMessage['result']['id'],
            ]);
            Cache::put(key: "amocrm_$receiver-{$sendMessage['result']['id']}", value: $sender->name, ttl: 86400);
            file_put_contents('cache2.txt', Cache::has("amocrm_$receiver-{$sendMessage['result']['id']}"));
            return response()->json(['message' => 'Webhook received successfully']);
        }

        return response()->json(['status' => 'error'], 500);
    }

    public function getMedia(TelegramService $telegram, $messageId, $phone)
    {
        $telegram->getMedia($messageId, "+$phone");
    }
}
