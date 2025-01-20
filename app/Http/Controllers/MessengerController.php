<?php

namespace App\Http\Controllers;
//
//use App\Http\Requests\MessengerStoreRequest;
//use App\Http\Requests\MessengerUpdateRequest;
use App\Http\Resources\MessengerCollection;
use App\Http\Resources\MessengerResource;
use App\Models\Messenger;
use App\Services\TelegramService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;
use Inertia\Response;


class MessengerController extends Controller
{
    public function index(): Response
    {
        $phone = auth()->user()->account->connections[0]->phone;
        $dialogs = $this->telegramService->getDialogs($phone);
        $chats = array_map(function ($dialog) {
            return [
                'id'     => $dialog["peer_id"],
                'type'   => $dialog['type'],
                'name'   =>  $dialog['title'],
                'time'   => now()->toDateTimeString(),
                'lastMessage' => $dialog['last_message'],
                'unread' => $dialog['unread_count'],
            ];
        }, $dialogs);

        return Inertia::render('Messengers/Index', [
            'chats' => $chats
        ]);
    }


    protected TelegramService $telegramService;

    public function __construct(TelegramService $telegramService)
    {
        $this->telegramService = $telegramService;
    }

    public function getMessages( Request $request): JsonResponse
    {

        $peerId = $request->integer("peerId");
        $phone = auth()->user()->account->connections[0]->phone;
        $messages = $this->telegramService->getMessages($phone, $peerId);
        return response()->json($messages);
    }

//    public function getDialogs(Request $request)
//    {
//        $validator = Validator::make($request->all(), [
//            'phone' => 'required|string|max:15'
//        ]);
//
//        $phone = $request->input('phone');
//        $dialogs = $this->telegramService->getDialogs($phone);
//
//        return response()->json($dialogs);
//    }

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
