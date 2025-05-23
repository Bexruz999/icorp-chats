<?php

namespace App\Http\Controllers\Api\Webhook;

use AmoJo\Webhook\ValidatorWebHooks;
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
        $sender = User::where('amojo_id', $amoMessage['sender']['id'])
            ->whereNotNull('telegram_id')
            ->firstOrFail();

        $receiver = Str::after($amoMessage['receiver']['client_id'], 'user-');

        $sendMessage = $telegram->sendMessage($sender->phone, $receiver, $amoMessage['message']['text']);

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
       $telegram->getMedia($messageId, $phone);
    }
}
