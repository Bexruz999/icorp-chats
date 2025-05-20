<?php

namespace App\Http\Controllers\Api\Webhook;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AmoChatController extends Controller
{
    public function handle(Request $request)
    {
        $signature = $request->header('X-Signature');

        $payload = $request->getContent();

        $calculatedSignature = hash_hmac('sha256', $payload, config('amo.secret_key'));

        if (!hash_equals($calculatedSignature, $signature)) {
            return response()->json(['message' => 'Invalid signature'], 403);
        }

        file_put_contents('webhook.json', $request->all());

        return response()->json(['message' => 'Webhook received successfully']);
    }
}
