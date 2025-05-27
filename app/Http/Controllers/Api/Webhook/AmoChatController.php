<?php

namespace App\Http\Controllers\Api\Webhook;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserMessage;
use App\Services\TelegramService;
use Illuminate\Http\Request;
use Str;

class AmoChatController extends Controller
{
    public function handle(Request $request, TelegramService $telegram)
    {
        if (empty($request->header('X-Signature'))) {
            return response()->json(['message' => 'Invalid signature'], 403);
        }

        $amoMessage = $request->post('message');
        //file_put_contents('wmedia.json', json_encode($amoMessage, JSON_PRETTY_PRINT));
        file_put_contents('wtext.json', json_encode($amoMessage, JSON_PRETTY_PRINT));
        $sender = User::where('amojo_id', $amoMessage['sender']['id'])
            ->whereNotNull('telegram_id')
            ->firstOrFail();

        $receiver = Str::after($amoMessage['receiver']['client_id'], 'user-');

        if ($amoMessage['message']['type'] == 'text') {
            $sendMessage = $telegram->sendMessage(
                phone: $sender->phone, peerId: $receiver, message: $amoMessage['message']['text']
            );
        } elseif (in_array($amoMessage['message']['type'], ['file', 'video', 'picture', 'audio'])) {
            $sendMessage = $telegram->sendMedia(
                phone: $sender->phone,
                peerId: $receiver,
                type: $telegram->getMediaTypeForMadelineProto($amoMessage['message']['file_name']),
                path: $amoMessage['message']['file_path'],
                fileName: $amoMessage['message']['file_name'], message: $amoMessage['message']['text']
            );

        } else {
            return response()->json(['status' => 'error', 'message' => 'Unsupported message type'], 400);
        }
        //$sendMessage = $telegram->sendMedia('', '', '', '', '');

        if ($sendMessage['success']) {
            UserMessage::create([
                'user_id' => $sender->id,
                'chat_id' => $receiver,
                'message_id' => $sendMessage['result']['id'],
            ]);
            return response()->json(['message' => 'Webhook received successfully']);
        }

        return response()->json(['status' => 'error'], 500);
    }

    public function getMedia(TelegramService $telegram, $messageId, $phone)
    {
        $telegram->getMedia($messageId, "+$phone");
    }
}
