<?php

namespace App\Http\Controllers\Api\Webhook;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\TelegramService;
use Illuminate\Http\Request;
use Str;

class AmoChatController extends Controller
{
    public function handle(Request $request, TelegramService $telegram)
    {
        $signature = $request->header('X-Signature');

        $payload = $request->getContent();

        $calculatedSignature = hash_hmac('sha256', $payload, config('amo.secret_key'));

        if (!hash_equals($calculatedSignature, $signature)) {
            return response()->json(['message' => 'Invalid signature'], 403);
        }

        $amoMessage = $request->post('message');
        $sender = User::where('amojo_id', $amoMessage['sender']['id'])
            ->whereNotNull('telegram_id')
            ->firstOrFail();
        $receiver = Str::after($amoMessage['receiver']['receiver'], 'user-');

        $telegram->sendMessage($sender->phone, $receiver, $amoMessage['message']['text']);

        return response()->json(['message' => 'Webhook received successfully']);
    }
}
