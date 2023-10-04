<?php

namespace App\Http\Services;

use App\Http\Requests\RoleUpdateRequest;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleService
{
    protected $role;

    public function __construct(Role $role)
    {
        $this->role = $role;
    }

    public function roles()
    {
        return $this->role->clone()
            ->with('permissions')->whereNot('id', 3)->get();
    }

    public function getRole($id)
    {
        return $this->role->clone()->with('permissions')->find($id);
    }

    public function updateRole(RoleUpdateRequest $request, $id): bool
    {
        $role = $this->role->clone()->findOrFail($id);

        if($role->name == 'Super Admin' || $role->name == 'Customer')
        {
            return false;
        }

        $role->update([
            'name' => $request->name
        ]);

        $role->syncPermissions($request->permissions);


        $users = User::query()->role($role->name);

        foreach($users as $user)
        {
            Cache::delete('permissions'.$user->id);
        }

        return true;
    }

    public function permissions()
    {
        return Permission::get();
    }
}
