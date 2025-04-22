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

        $user = User::find(2);

        $account = $user->account;

        //$role = Role::create(['name' => 'admin', 'team_id' => $account->id]);

        $role = Role::find(1);
        //$user->assignRole($role);
        setPermissionsTeamId($user->account_id);
        dd($user->hasRole('admin'));


        return Inertia::render('Dashboard/Index');
    }
}
