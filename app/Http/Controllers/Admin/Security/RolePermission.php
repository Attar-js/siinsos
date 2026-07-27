<?php

namespace App\Http\Controllers\Admin\Security;
use App\Http\Controllers\Admin\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermission extends Controller
{
    public function index(Request $request)
    {
        $roles = Role::get();
        $permissions = Permission::get();
        return view('admin.role-permission.permissions', compact('roles', 'permissions'));
    }

    public function store(Request $request)
    {
        //code here
    }
}
