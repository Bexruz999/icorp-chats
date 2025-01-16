<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Mail\SendPassword;
use App\Models\User;
use App\Providers\AppServiceProvider;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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

        $user = User::create([
            'first_name' => $request->first_name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => bcrypt($password),
            'account_id' => 1,
            'owner' => true
        ]);

        Mail::to($request->email)->send(new SendPassword($request->email, $password, $request->email));

        return redirect()->route('login');
    }
}