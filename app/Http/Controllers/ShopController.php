<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreShopRequest;
use App\Http\Requests\UpdateShopRequest;
use App\Http\Resources\ShopCollection;
use App\Http\Resources\ShopResource;
use App\Models\Shop;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use function Symfony\Component\Translation\t;

class ShopController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Inertia::render('Shops/Index', [
            'filters' => \Illuminate\Support\Facades\Request::all('search', 'role', 'trashed'),
            'shops' => new ShopCollection(Auth::user()->account->shops()->with(['bot', 'account'])->paginate()),

        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $bots = Auth::user()->account->bots()->doesntHave('shops')->pluck('name', 'id');

        $options = [];
        if (isset($bots)) {
            foreach ($bots as $id => $bot) {
                $options[] = ['label' => $bot, 'value' => $id];
            }
        }

        return Inertia::render('Shops/Create')->with([
            'bots' => $options
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreShopRequest $request)
    {
        $validated = $request->validated();

        $shop = Auth::user()->account->shops()->create([
            'name' => $validated['name'],
            'bot_id' => $validated['bot_id'],
            'slug' => Str::random()
        ]);

        return redirect()->route('shops.edit', $shop->id)->with('success', 'Магазин добавлен успешно');
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
        $shop = Shop::with('categories', 'bot')->find($id);

        $bots = Auth::user()->account->bots()->doesntHave('shops')->pluck('name', 'id');

        $options[] = ['label' => 'Нет', 'value' => null, 'selected' => true];
        if (isset($bots)) {
            foreach ($bots as $id => $bot) {
                $options[] = [
                    'label' => $bot,
                    'value' => $id,
                    'selected' => $shop->bot_id == $id
                ];
            }
        }

        if ($shop->bot()->exists()) {
            $options = array_merge($options, [
                ['label' => $shop->bot->name, 'value' => $shop->bot->id, 'selected' => true]
            ]);
        }

        return Inertia::render('Shops/Edit', [
            'shop' => new ShopResource($shop),
            'bots' => $options
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateShopRequest $request, string $id)
    {
        $shop = Shop::find($id);

        $shop->update($request->validated());

        return redirect()->route('shops.index')->with('success', 'Магазин изменен успешно');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
