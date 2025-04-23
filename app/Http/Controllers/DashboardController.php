<?php

namespace App\Http\Controllers;

use App\Models\User;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Role;

class DashboardController extends Controller
{

    public function index(): Response
    {

        /*$account = $user->account;

        //$role = Role::create(['name' => 'admin', 'team_id' => $account->id]);

        $role = Role::find(1);
        //$user->assignRole($role);
       */
        dd(auth()->user()->roles()->get());


        return Inertia::render('Dashboard/Index');
    }
}
