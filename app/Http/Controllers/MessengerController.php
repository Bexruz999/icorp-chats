<?php

namespace App\Http\Controllers;
//
//use App\Http\Requests\MessengerStoreRequest;
//use App\Http\Requests\MessengerUpdateRequest;
use App\Http\Resources\MessengerCollection;
use App\Http\Resources\MessengerResource;
use App\Models\Messenger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Request;
use Inertia\Inertia;
use Inertia\Response;

class MessengersController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Messengers/Index', [
            'filters' => Request::all('search', 'trashed'),
            'messengers' => new MessengerCollection(
                Messenger::query()
                    ->orderBy('name')
                    ->filter(Request::only('search', 'trashed'))
                    ->paginate()
                    ->appends(Request::all())
            ),
        ]);
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
