<?php

namespace App\Http\Controllers;
//
//use App\Http\Requests\MessengerStoreRequest;
//use App\Http\Requests\MessengerUpdateRequest;
use App\Events\TelegramMessageShipped;
use App\Http\Resources\MessengerCollection;
use App\Http\Resources\MessengerResource;
use App\Models\Messenger;
use App\Models\UserMessage;
use App\Services\TelegramService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;
use Inertia\Response;
use danog\MadelineProto\API;
use App\Listeners\TelegramIncomingMessage;




class MessengerController extends Controller
{

    public function index(): Response
    {
        $phone = auth()->user()->account->connections()->first()->phone;

        $api = new TelegramService;
        //$api = new API(storage_path("app/telegram/{$phone}.madeline"));
        //$chat = $api->messages->getDialogs();

        $chats = $api->getDialogs($phone);

        //$handler = $api->getEventHandler(TelegramIncomingMessage::class);

        //$chats = $handler->getDialogs();

        //var_dump($chats);

        //$handler = new TelegramIncomingMessage();


        return Inertia::render('Messengers/Index', [
            'chats' => $chats
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



    public function sendMessage(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'peerId' => 'required|integer',
            'message' => 'required|string',
        ]);

        $user = auth()->user();

        $phone = $user->account->connections[0]->phone;
        $result = $this->telegramService->sendMessage($phone, $validated['peerId'], $validated['message']);

        if ($result['success']) {

            $userMessage = UserMessage::create([
                'user_id' => $user->id,
                'chat_id' => $validated['peerId'],
                'message_id' => $result['message_id'],
            ]);

            TelegramMessageShipped::dispatch($userMessage, $validated['message'], $user);

            return response()->json(['status' => 'success', 'message_id' => $result['message_id']]);
        }

        return response()->json(['status' => 'error', 'error' => $result['error']], 500);
    }


    public function create(): Response
    {
        return Inertia::render('Messengers/Create');
    }

    public function store(MessengerStoreRequest $request): RedirectResponse
    {
        Messenger::create(
            $request->validated()
        );

        return Redirect::route('messengers')->with('success', 'Messenger created.');
    }

    public function edit(Messenger $messenger): Response
    {
        return Inertia::render('Messengers/Edit', [
            'messenger' => new MessengerResource($messenger),
        ]);
    }

    public function update(Messenger $messenger, MessengerUpdateRequest $request): RedirectResponse
    {
        $messenger->update(
            $request->validated()
        );

        return Redirect::back()->with('success', 'Messenger updated.');
    }

    public function destroy(Messenger $messenger): RedirectResponse
    {
        $messenger->delete();

        return Redirect::back()->with('success', 'Messenger deleted.');
    }

    public function restore(Messenger $messenger): RedirectResponse
    {
        $messenger->restore();

        return Redirect::back()->with('success', 'Messenger restored.');
    }
}
