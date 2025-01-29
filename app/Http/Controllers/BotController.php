<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBotRequest;
use App\Http\Resources\BotCollection;
use App\Http\Resources\BotResource;
use App\Models\Bot;
use App\Models\Shop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Telegram\Bot\Api;
use Telegram\Bot\Keyboard\Keyboard;

class BotController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Inertia::render('Bots/Index', [
            'filters' => \Illuminate\Support\Facades\Request::all('search', 'role', 'trashed'),
            'bots' => new BotCollection(
                Auth::user()->account->bots()->paginate()
            ),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Bots/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreBotRequest $request)
    {
        $slug = strtolower(Str::random(16));
        $token = $request->token;
        $url = route('bot.webhook', ['slug' => $slug]);

        $telegram = new Api($token);

        try {
            $result = $telegram->setWebhook(['url' => $url]);

            if ($result->ok) {
                Log::info('Bot webhook set for '. $slug);
                Auth::user()->account->bots()->create(array_merge(
                    $request->validated(),
                    ['slug' => $slug]
                ));
            } else {
                Log::error('Error setting bot webhook for '. $slug. ': '. $result->description);
            }

            return redirect()->route('bots.index')->with('success', 'Бот успешно добавлен');
        } catch (\Throwable $exception) {
            return redirect()->back()->with('error', $exception);
        }


    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $bot = Bot::find($id);

        return Inertia::render('Bots/Edit', [
            'bot' => new BotResource($bot)
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function webhook(Request $request, string $slug) {
        $bot = Bot::where('slug', $slug)->firstOrFail();

        $reply_markup = Keyboard::make()
            ->setResizeKeyboard(true)
            ->setOneTimeKeyboard(true)
            ->row([
                Keyboard::button('1'),
                Keyboard::button('2'),
                Keyboard::button('3'),
            ])
            ->row([
                Keyboard::button('4'),
                Keyboard::button('5'),
                Keyboard::button('6'),
            ])
            ->row([
                Keyboard::button('7'),
                Keyboard::button('8'),
                Keyboard::button('9'),
            ])
            ->row([
                Keyboard::button('0'),
            ]);

        $telegram = new Api($bot->token);
        $res = $telegram->sendMessage([
            'chat_id' => 781366976,
            'text' => 'test',
            'reply_markup' => $reply_markup
        ]);

        Log::info(json_encode($res));

        return response()->json(['ok' => true]);
    }

    public function shop(Request $request, $slug) {
        $shop = Shop::with('categories', 'bot')->where(['slug' => $slug])->firstOrFail();

        return Inertia::render('MiniApp/Index')->with([
            'data' => $shop->categories()->with('products')->get(),
            'bot' => $shop->bot
        ]);
    }
}
