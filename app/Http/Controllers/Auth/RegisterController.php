<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Mail\SendPassword;
use App\Models\Account;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class RegisterController extends Controller
{
    /**
     * Display the login view.
     */
    public function index(): Response
    {
        return Inertia::render('Auth/Register');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(RegisterRequest $request)
    {

        $password = Str::password(8, true, true, false);

        $account = Account::create([
            'name' => $request->first_name,
        ]);

        $user = User::create([
            'first_name' => $request->first_name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => bcrypt($password),
            'account_id' => $account->id,
            'owner' => true
        ]);

        Mail::to($request->email)->send(new SendPassword($request->email, $password, $request->email));

        return redirect()->route('login');
    }
}
