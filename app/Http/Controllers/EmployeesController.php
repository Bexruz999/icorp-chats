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
        $amoUsers = $amoApiService->getAmoAccount();

        //if (!$user->hasRole('admin'))  abort(419);

        return Inertia::render('Employees/Create', [
            'connections' => $user->account->connections,
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

        // Localda oddiy massivdan amo_users
        if (app()->environment('local')) {
            $amoUsers = [
                ['amojo_id' => 'local_1', 'name' => 'Local User 1'],
                ['amojo_id' => 'local_2', 'name' => 'Local User 2'],
            ];
        } else {
            $amoApiService = new AmoApiService($user);
            $amoUsers = $amoApiService->getAmoAccount();
        }

        return Inertia::render('Employees/Edit', [
            'user' => new UserResource($user),
            'amo_users' => $amoUsers,
            'connections' => $auth->account->connections,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UserUpdateRequest $request, $id)
    {
        $user = User::findOrFail($id);

        $user->update($request->validated());

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
