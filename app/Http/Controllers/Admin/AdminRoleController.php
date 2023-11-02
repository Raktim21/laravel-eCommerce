<?php

namespace App\Http\Controllers\Admin;

use App\Http\Services\RoleService;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use App\Http\Requests\RoleUpdateRequest;

class AdminRoleController extends Controller
{

    protected $service;

    public function __construct(RoleService $service)
    {
        $this->service = $service;
    }


    public function permissionList()
    {
        $data = Cache::rememberForever('permissions', function () {
            return $this->service->permissions();
        });

        return response()->json([
            'status' => true,
            'data'   => $data,
        ]);
    }


    public function roleList()
    {
        $data = Cache::remember('adminRoles', 24*60*60*60, function () {
            return $this->service->roles();
        });

        return response()->json([
            'status' => true,
            'data'   => $data,
        ]);
    }



    public function roleDetail($id)
    {
        $data = Cache::remember('roleDetail'.$id, 24*60*60*60, function () use ($id) {
            return $this->service->getRole($id);
        });

        return response()->json([
            'status'=> true,
            'data'  => $data,
        ], is_null($data) ? 204 : 200);
    }


    public function roleUpdate(RoleUpdateRequest $request, $id)
    {
        if($this->service->updateRole($request, $id))
        {
            Cache::delete('roleDetail'.$id);
            Cache::delete('adminRoles');

            return response()->json([
                'status' => true,
            ]);
        }
        return response()->json([
            'status'        => false,
            'errors'        => ['Role of super admin or customer can not be updated.']
        ], 400);
    }
}
