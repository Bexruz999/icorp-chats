<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBasketRequest;
use App\Http\Requests\StoreBotRequest;
use App\Http\Requests\UpdateBotRequest;
use App\Http\Resources\BotCollection;
use App\Http\Resources\BotResource;
use App\Models\Basket;
use App\Models\Bot;
use App\Models\Product;
use App\Models\Shop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Telegram\Bot\Api;
use Telegram\Bot\Exceptions\TelegramSDKException;

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

        try {
            $telegram = new Api($token);
        } catch (TelegramSDKException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }

        try {
            $result = $telegram->getMe();

            if ($result->isBot) {
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
    public function update(UpdateBotRequest $request, string $id)
    {
        $bot = Bot::find($id);

        $bot->update($request->validated());

        return redirect()->route('bots.index')->with('success', 'Бот успешно изменен');
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

        $telegram = new Api($bot->token);
        $res = $telegram->sendMessage([
            'chat_id' => 781366976,
            'text' => 'test'
        ]);

        Log::info(json_encode($res));

        return response()->json(['ok' => true]);
    }

    public function shop($slug) {
        $shop = Shop::with('categories', 'bot')->where(['slug' => $slug])->firstOrFail();

        return Inertia::render('MiniApp/Index')->with([
            'categories' => $shop->categories()->with('products')->get(),
            'bot' => $shop->bot,
            'slug' => $slug
        ]);
    }

    public function addBasket(StoreBasketRequest $request, $slug)
    {
        $shop = Shop::select(['id', 'slug', 'account_id', 'bot_id'])->where('slug', $slug)->firstOrFail();


        $validated = $request->validated();

        $basket = new Basket();

        $basket->shop_id = $shop->id;
        $basket->account_id = $shop->account_id;
        $basket->tg_id = $validated['tg_id'];
        $basket->description = $validated['description'];
        $basket->save();


        foreach ($validated['basket'] as $key => $item) {

            $product = Product::findOrFail($key);


            $price = ($product->price > $product->discount_price && $product->discount_price > 0) ?
                $product->discount_price : $product->price;

            $basket->items()->create([
                'product_id' => $key,
                'quantity' => $item,
                'price' => $price,
            ]);
        }

        $text = "🛍 Корзина";
        $total  = 0;
        foreach ($basket->items as $item) {

            $text.= "\n $item->quantity x $item->price $shop->currency: ".$item->product->name;

            $total += $item->price * $item->quantity;

        }

        $text.= "\n\n💳 Итого:  ".$total." $shop->currency";

        if ($shop->bot()->exists()) {
            $telegram = new Api($shop->bot->token);
            $telegram->sendMessage([
                'chat_id' => $validated['tg_id'],
                'text' => $text
            ]);

            return redirect()->back()->with('success', 'Заказ Оформлен');
        }

        return response()->json(['success' => false]);
    }
}
