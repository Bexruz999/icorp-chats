<?php

namespace App\Http\Controllers;


use App\Models\UserMessage;
use App\Services\TelegramService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;


class MessengerController extends Controller
{
    protected TelegramService $telegramService;

    public function __construct(TelegramService $telegramService)
    {
        $this->telegramService = $telegramService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): Response
    {
        $phone = auth()->user()->getPhone();

        try {
            return Inertia::render('Messengers/Index', [
                'chats' => $this->telegramService->getDialogs($phone)
            ]);
        } catch (Exception $e) {
            return Inertia::render('Error', ['status' => $e->getCode()]);
        }
    }

    public function getMessages(Request $request): JsonResponse
    {
        $peerId = $request->integer('peerId');
        $phone = auth()->user()->getPhone();

        $messages = $this->telegramService->getMessages($phone, $peerId);
        return response()->json($messages);
    }


    public function sendMessage(Request $request): JsonResponse
    {
        $valid = $request->validate(['peerId' => 'required|integer', 'message' => 'required|string']);

        $user = auth()->user();
        $phone = $user->getPhone();
        $data = $this->telegramService->sendMessage($phone, $valid['peerId'], $valid['message']);

        if ($data['success']) {

            UserMessage::create([
                'user_id' => $user->id,
                'chat_id' => $valid['peerId'],
                'message_id' => $data['result']['id'],
            ]);

            return response()->json(['status' => 'success', 'message_id' => $data['result']['id'], 'data' => $data]);
        }

        return response()->json(['status' => 'error', 'error' => $data['error']], 500);
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
                type: $this->telegramService->getMediaTypeForMadelineProto($file),
                chatId: $validated['peer_id'],
                path: storage_path('app/public/' . $filePath),
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

    public function getMedia($message_id): void
    {
        $this->telegramService->getMedia($message_id);
    }
}
