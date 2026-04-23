<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminRole;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class RoleController extends Controller
{
    public function index()
    {
        $roles    = AdminRole::withCount('users')->get();
        $subAdmins = User::where('global_role', 'sub_admin')->with('adminRole')->get();

        return view('admin.roles.index', compact('roles', 'subAdmins'));
    }

    public function create()
    {
        $permissions = config('admin.permissions');

        return view('admin.roles.create', compact('permissions'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'        => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:255'],
            'permissions' => ['nullable', 'array'],
        ]);

        AdminRole::create([
            'name'        => $data['name'],
            'slug'        => Str::slug($data['name']),
            'description' => $data['description'] ?? null,
            'permissions' => $data['permissions'] ?? [],
        ]);

        return redirect()->route('admin.roles.index')->with('success', 'Role created successfully.');
    }

    public function edit(AdminRole $role)
    {
        $permissions = config('admin.permissions');

        return view('admin.roles.edit', compact('role', 'permissions'));
    }

    public function update(Request $request, AdminRole $role)
    {
        $data = $request->validate([
            'name'        => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:255'],
            'permissions' => ['nullable', 'array'],
        ]);

        $role->update([
            'name'        => $data['name'],
            'slug'        => Str::slug($data['name']),
            'description' => $data['description'] ?? null,
            'permissions' => $data['permissions'] ?? [],
        ]);

        return redirect()->route('admin.roles.index')->with('success', 'Role updated successfully.');
    }

    public function destroy(AdminRole $role)
    {
        $role->users()->update(['admin_role_id' => null, 'global_role' => 'customer']);
        $role->delete();

        return redirect()->route('admin.roles.index')->with('success', 'Role deleted.');
    }

    // Sub-admin user management
    public function createSubAdmin()
    {
        $roles = AdminRole::all();

        return view('admin.roles.create-sub-admin', compact('roles'));
    }

    public function storeSubAdmin(Request $request)
    {
        $data = $request->validate([
            'name'          => ['required', 'string', 'max:255'],
            'email'         => ['required', 'email', 'unique:users'],
            'password'      => ['required', 'string', 'min:8', 'confirmed'],
            'admin_role_id' => ['required', 'exists:admin_roles,id'],
        ]);

        User::create([
            'name'          => $data['name'],
            'email'         => $data['email'],
            'password'      => $data['password'],
            'global_role'   => 'sub_admin',
            'admin_role_id' => $data['admin_role_id'],
            'is_active'     => true,
        ]);

        return redirect()->route('admin.roles.index')->with('success', 'Sub-admin created successfully.');
    }
}
