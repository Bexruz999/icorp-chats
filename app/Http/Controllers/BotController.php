<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBotRequest;
use App\Http\Resources\BotCollection;
use App\Http\Resources\BotResource;
use App\Models\Bot;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Telegram\Bot\Laravel\Facades\Telegram;

class BotController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Inertia::render('Bots/Index', [
            'filters' => \Illuminate\Support\Facades\Request::all('search', 'role', 'trashed'),
            'users' => new BotCollection(
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
        Auth::user()->account->bots()->create($request->validated());

        $token = $request->token;
        $url = route('bot.webhook');
        $response = file_get_contents("https://api.telegram.org/bot$token/setWebhook?url=$url");

        Log::info($response);

        return Response::json($response);
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
}
