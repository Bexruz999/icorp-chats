<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();
        if (true) {
            return Inertia::render('Roles/Index', [
                'roles' => Role::where('team_id', $user->account_id)->get(),
            ]);
        } else {
            abort(419);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $user = Auth::user();
        if (true) {
            return Inertia::render('Roles/Create', [
                'permissions' => Permission::all(),
            ]);
        } else {
            abort(419);
        }
    }

    /**
     * Store a new role and assign permissions using Spatie Laravel-Permission.
     *
     * @param  \Illuminate\Http\Request  $request
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        // Validate the incoming request data.
        $validated = $request->validate([
            'roleName' => [
                'required',
                Rule::unique('roles', 'name')->where(function ($query) use ($user) {
                    return $query->where('team_id', $user->account_id);
                })],
            'permissions' => 'required|array',
            'permissions.*.key' => 'required|string',
            'permissions.*.label' => 'required|string',
            'permissions.*.view' => 'nullable|boolean',
            'permissions.*.edit' => 'nullable|boolean',
            'permissions.*.remove' => 'nullable|boolean',
        ]);

        // Create a new role using the Spatie package.
        $role = Role::create(['name' => $validated['roleName'], 'team_id' => $user->account_id]);

        // Loop through each permission row to assign permissions.
        foreach ($validated['permissions'] as $permissionRow) {

            $menuKey = $permissionRow['key'];

            // If 'view' permission is true, create/assign the permission.
            if (!empty($permissionRow['view'])) {
                $permName = "view {$menuKey}";
                $permission = Permission::firstOrCreate(['name' => $permName]);
                $role->givePermissionTo($permission);
            }

            // If 'edit' permission is true, create/assign the permission.
            if (!empty($permissionRow['edit'])) {
                $permName = "edit {$menuKey}";
                $permission = Permission::firstOrCreate(['name' => $permName]);
                $role->givePermissionTo($permission);
            }

            // If 'remove' permission is true, create/assign the permission.
            if (!empty($permissionRow['remove'])) {
                $permName = "remove {$menuKey}";
                $permission = Permission::firstOrCreate(['name' => $permName]);
                $role->givePermissionTo($permission);
            }
        }

        // Return a JSON response confirming the role has been added.
        return response()->redirectToRoute('roles.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(Role $role)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Role $role)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Role $role)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Role $role)
    {
        //
    }
}
