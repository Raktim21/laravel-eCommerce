<?php

namespace App\Http\Controllers\Admin;

use App\Http\Services\AssetService;
use App\Http\Services\UserService;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use App\Http\Requests\AdminCreateRequest;
use App\Http\Requests\AvatarUpdateRequest;
use App\Http\Requests\PickupAddressRequest;
use App\Http\Requests\AdminBulkDeleteRequest;
use App\Http\Requests\UserProfileUpdateRequest;

class AdminController extends Controller
{
    protected $service;
    public function __construct(UserService $service)
    {
        $this->service = $service;
    }


    public function adminList()
    {
        $data = $this->service->getAllUser(true);

        return response()->json([
            'status'  => true,
            'data'  => $data
        ], $data->isEmpty() ? 204 : 200);
    }



    public function adminCreate(AdminCreateRequest $request)
    {
        if (!$request->hasAny(['avatar', 'image_id']))
        {
            return response()->json([
                'status' => false,
                'errors' => ['Please select an image.']
            ], 422);
        }

        if($this->service->storeAdmin($request))
        {
            return response()->json([
                'status'  => true,
            ], 201);
        }

        return response()->json([
            'status'  => false,
            'errors'  => ['Something went wrong']
        ], 500);
    }


    public function adminDetail($id)
    {
        $data = Cache::remember('adminDetail'.$id, 24*60*60, function () use ($id) {
            return $this->service->show($id, true);
        });

        if($data && is_null($data['shop_branch_id']))
        {
            return response()->json([
                'status' => true,
                'errors' => ['No Admin Found.'],
            ], 404);
        }

        return response()->json([
            'status' => true,
            'data' => $data,
        ], is_null($data) ? 204 : 200);
    }


    public function adminUpdate(UserProfileUpdateRequest $request, $id)
    {
        $this->service->update($request, $id, false, true);
        Cache::forget('adminDetail'.$id);

        return response()->json([
            'status'  => true,
        ]);
    }


    public function adminUpdateAvatar(AvatarUpdateRequest $request, $id)
    {
        $this->service->updateAvatar($request, $id, true);

        return response()->json([
            'status'  => true,
        ]);

    }


    public function adminDelete($id)
    {
        if($this->service->deleteAdmin($id))
        {
            return response()->json([
                'status' => true,
            ]);
        }
        return response()->json([
            'status' => false,
            'errors' => ['Super admins cannot be deleted.']
        ], 400);
    }



    public function pickUpAddress()
    {
        $data = $this->service->adminAddress(request()->input('branch_id') ?? auth()->user()->shop_branch_id);

        return response()->json([
            'status'  => true,
            'data' => $data
        ], is_null($data) ? 204 : 200);
    }


    public function pickUpAddressUpdate(PickupAddressRequest $request)
    {
        if((new AssetService())->activeDeliverySystem() == 2 && !$request->hub_id)
        {
            return response()->json([
                'status' => false,
                'errors' => ['eCourier Hub configuration is missing.']
            ], 400);
        }
        $this->service->updateAdminAddress($request);

        return response()->json([
            'status' => true,
        ]);
    }

    public function bulkDelete(AdminBulkDeleteRequest $request)
    {
        $this->service->deleteAdmins($request);

        return response()->json([
            'status' => true,
        ]);
    }
}
