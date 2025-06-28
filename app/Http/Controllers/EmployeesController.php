<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserStoreRequest;
use App\Http\Requests\UserUpdateRequest;
use App\Http\Resources\UserCollection;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\AmoApiService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Request;
use Inertia\Inertia;

class EmployeesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();

        return Inertia::render('Employees/Index', [
            'filters' => Request::all('search', 'role', 'trashed'),
            'users' => new UserCollection(
                $user->account->users()->whereNot('id', $user->id)->paginate()
            ),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(AmoApiService $amoApiService)
    {
        $user = Auth::user();

        $connections = $user->account->connections;
        // If no connections, send empty array
        if (!$connections || $connections->isEmpty()) {
            $connections = [];
        }

        $hasAmoConnection = $user->account->amoConnections()
            ->whereNotNull(['access_token', 'refresh_token'])->exists();

        $amoUsers = $hasAmoConnection ? $amoApiService->getAmoAccount() : [];

        return Inertia::render('Employees/Create', [
            'connections' => $connections,
            'amoUsers' => $amoUsers,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(UserStoreRequest $request)
    {
        $user = Auth::user();
        //if (!$user->hasRole('admin'))  abort(419);

        $user->account->users()->create($request->validated());

        /*if ($request->hasFile('photo')) {
            $user->update([
                'photo' => $request->file('photo')->store('users'),
            ]);
        }*/

        return Redirect::route('employees.index')->with('success', 'Сотрудник создан.');
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $auth = Auth::user();
        $user = User::findOrFail($id);

        if (!$auth->hasRole('admin') && $user->owner) abort(419);

        if (app()->environment('local') || ) {
            $amoUsers = [
                ['amojo_id' => 'e7123126-d5eb-4df2-a146-1c702c17c3c4', 'name' => 'Local User 1'],
                ['amojo_id' => 'local_2', 'name' => 'Local User 2'],
            ];
        } else {
            $amoApiService = new AmoApiService($user);
            $hasAmoConnection = $user->account->amoConnections()
                ->whereNotNull(['access_token', 'refresh_token'])->exists();

            $amoUsers = $hasAmoConnection ? $amoApiService->getAmoAccount() : [];
        }

        $connections = $auth->account->connections;
        // If no connections, send empty array
        if (!$connections || $connections->isEmpty()) {
            $connections = [];
        }

        // If no amoUsers, send empty array
        if (!$amoUsers || empty($amoUsers)) {
            $amoUsers = [];
        }

        return Inertia::render('Employees/Edit', [
            'user' => new UserResource($user),
            'amo_users' => $amoUsers,
            'connections' => $connections,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UserUpdateRequest $request, $id)
    {
        $validated = $request->validated();
        $user = User::findOrFail($id);

        $amo_connection_id = $user->account->amoConnections()->first()->id;

        $validated['amo_connection_id'] = $amo_connection_id;

        $user->update($validated);

        if ($request->hasFile('photo')) {
            $user->update(['photo' => $request->file('photo')->store('users')]);
        }

        return Redirect::back()->with('success', 'Сотрудник обновлен.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        //
    }
}
