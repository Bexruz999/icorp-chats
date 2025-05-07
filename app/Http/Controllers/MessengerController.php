<?php

namespace App\Http\Controllers;

use App\Models\UserMessage;
use App\Services\AmoChatService;
use App\Services\TelegramService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;


class MessengerController extends Controller
{

    public function index(): Response
    {
        $phone = auth()->user()->account->connections()->first()->phone;

        return Inertia::render('Messengers/Index', [
            'chats' => $this->telegramService->getDialogs($phone)
        ]);
    }


    protected TelegramService $telegramService;

    public function __construct(TelegramService $telegramService)
    {
        $this->telegramService = $telegramService;
    }


    public function getMessages(Request $request): JsonResponse
    {
        $peerId = $request->integer('peerId');
        $phone = auth()->user()->account->connections[0]->phone;

        $messages = $this->telegramService->getMessages($phone, $peerId);
        return response()->json($messages);
    }


    public function sendMessage(Request $request, AmoChatService $amoChatService): JsonResponse
    {
        $valid = $request->validate(['peerId' => 'required|integer', 'message' => 'required|string']);

        $user = auth()->user();
        $phone = $user->account->connections[0]->phone;
        $result = $this->telegramService->sendMessage($phone, $valid['peerId'], $valid['message']);

        if ($result['success']) {

            UserMessage::create([
                'user_id'       => $user->id,
                'chat_id'       => $valid['peerId'],
                'message_id'    => $result['message_id']
            ]);

           /* $amoChatService->sendMessage(contact: [
                'id' => $valid['peerId'],
                'name' => $user->name
            ], msg_id: $result['message_id'], msg: $valid['message']);*/

            return response()->json(['status' => 'success', 'message_id' => $result['message_id']]);
        }

        return response()->json(['status' => 'error', 'error' => $result['error']], 500);
    }

    public function sendMedia(Request $request)
    {
        $validated = $request->validate([
            'peer_id' => 'required|numeric',
            'message' => 'nullable|string|max:255'
        ]);

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $filePath = $file->store('uploads');

            $this->telegramService->sendMedia(
                mediaType: $this->telegramService->getMediaTypeForMadelineProto($file),
                chatId: $validated['peer_id'],
                uploadPath: storage_path('app/public/' . $filePath),
                fileName: $file->getClientOriginalName(),
                message: $validated['message']
            );
        }
        return response()->json([
            'success' => true,
            'message' => $request->file('file')->getClientOriginalName(),
            'uuid' => $request->file_uuid
        ]);
    }

    public function sendVoice(Request $request)
    {
        $validated = $request->validate([
            'peer_id' => 'required|numeric',
            'file' => 'required|file|mimes:mp3,ogg,wav'
        ]);

        $file = $request->file('file');
        $filePath = $file->store('uploads');

        $this->telegramService->sendVoice(
            chatId: $validated['peer_id'],
            file: storage_path('app/public/' . $filePath),
            fileName: $file->getClientOriginalName()
        );

        return response()->json([
            'success' => true,
            'message' => $file->getClientOriginalName(),
        ]);
    }

    public function getMedia($message_id)
    {
        $this->telegramService->getMedia($message_id);
    }
}
