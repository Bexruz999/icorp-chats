<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserStoreRequest;
use App\Http\Requests\UserUpdateRequest;
use App\Http\Resources\UserCollection;
use App\Http\Resources\UserResource;
use App\Jobs\SendGeneratedPasswordToEmail;
use App\Mail\SendPassword;
use App\Models\User;
use App\Services\AmoApiService;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Request;
use Inertia\Inertia;
use Inertia\Response;
use Log;
use Mail;
use Str;

class EmployeesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): Response
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
    public function create()
    {
        $user = Auth::user();

        if (!$user->hasRole('admin')) {
            abort(419);
        }

        $amoApiService = new AmoApiService($user);

        $connections = $user->account->connections;
        // If no connections, send empty array
        if (!$connections || $connections->isEmpty()) {
            $connections = [];
        }

        $hasAmoConnection = $user->account->amoConnections()
            ->whereNotNull(['access_token', 'refresh_token'])->exists();

        try {
            $amoUsers = $hasAmoConnection ? $amoApiService->getAmoAccount() : [];
        } catch (GuzzleException $e) {
            Log::error($e->getMessage());
        } catch (ConnectionException $e) {
            Log::error('Connection error: ' . $e->getMessage());
            return Redirect::back()->withErrors([
                'amo_connection' => 'Ошибка подключения к AmoCRM. Проверьте соединение.',
            ]);
        }

        return Inertia::render('Employees/Create', [
            'connections' => $connections,
            'amoUsers' => $amoUsers ?? [],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(UserStoreRequest $request): RedirectResponse
    {
        $user = Auth::user();
        if (!$user->hasRole('admin')) {
            abort(419);
        }

        $validated = $request->validated();
        $validated['amo_connection_id'] = $user->account->amoConnections()->first()->id ?? null;

        Log::debug('employes validated: ' . json_encode($validated));

        if (empty($validated['password'])) {
            // Generate a random password
            $validated['password'] = Str::random(10);
            // Optionally, send the password to the user's email
            SendGeneratedPasswordToEmail::dispatch($validated['email'], $validated['password']);
        }

        $user->account->users()->create($validated);

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
    public function show(User $user): void
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

        if ($user->owner && !$auth->hasRole('admin')) {
            abort(419);
        }

        if (app()->environment('local')) {
            $amoUsers = [
                ['amojo_id' => 'e7123126-d5eb-4df2-a146-1c702c17c3c4', 'name' => 'Local User 1'],
                ['amojo_id' => 'local_2', 'name' => 'Local User 2'],
            ];
        } else {
            $amoApiService = new AmoApiService($user);
            $hasAmoConnection = $user->account->amoConnections()
                ->whereNotNull(['access_token', 'refresh_token'])->exists();

            try {
                $amoUsers = $hasAmoConnection ? $amoApiService->getAmoAccount() : [];
            } catch (GuzzleException $e) {
                Log::error($e->getMessage());
            } catch (ConnectionException $e) {
                Log::error('Connection error: ' . $e->getMessage());
                return Redirect::back()->withErrors([
                    'amo_connection' => 'Ошибка подключения к AmoCRM. Проверьте соединение.',
                ]);
            }
        }

        $connections = $auth->account->connections;
        // If no connections, send empty array
        if (!$connections || $connections->isEmpty()) {
            $connections = [];
        }

        // If no amoUsers, send empty array
        if (empty($amoUsers)) {
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
    public function update(UserUpdateRequest $request, $id): RedirectResponse
    {
        $validated = $request->validated();
        $user = User::findOrFail($id);


        $connection = $user->account->connections()
            ->where('id', $validated['connection_id'] ?? null)
            ->first();
        $amo_connection_id = $user->account->amoConnections()->first()->id;

        $validated['amo_connection_id'] = $amo_connection_id;
        $validated['telegram_id'] = $connection->telegram_id ?? null;

        $user->update($validated);


        return Redirect::back()->with('success', 'Сотрудник обновлен.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user): void
    {
        //
    }
}
