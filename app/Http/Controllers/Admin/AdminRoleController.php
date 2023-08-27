<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\RoleCreateRequest;
use App\Http\Requests\RoleUpdateRequest;
use App\Http\Services\RoleService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class AdminRoleController extends Controller
{

    protected $service;

    public function __construct(RoleService $service)
    {
        $this->service = $service;
    }


    public function roleList()
    {
        return response()->json([
            'status' => true,
            'data'   => $this->service->roles(),
        ]);
    }



    public function roleDetail($id)
    {
        $data = $this->service->getRole($id);
        return response()->json([
            'status'=> true,
            'data'  => $data,
        ], is_null($data) ? 204 : 200);
    }


    public function roleUpdate(RoleUpdateRequest $request, $id)
    {
        if($this->service->updateRole($request, $id)) {
            return response()->json([
                'status' => true,
            ]);
        }
        return response()->json([
            'status'        => false,
            'errors'        => ['Role of super admin or customer can not be updated.']
        ], 400);
    }


    public function permissionList()
    {
        return response()->json([
            'status' => true,
            'data'   => $this->service->permissions(),
        ]);
    }

}
