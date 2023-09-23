<?php

namespace App\Http\Controllers\Admin;

use App\Http\Services\AuthService;
use App\Http\Services\UserService;
use App\Http\Requests\DateRequest;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use App\Http\Requests\AvatarUpdateRequest;
use App\Http\Requests\UserBulkDeleteRequest;
use App\Http\Requests\UserRegistrationRequest;
use App\Http\Requests\UserAddressCreateRequest;
use App\Http\Requests\UserProfileUpdateRequest;
use App\Http\Requests\UserAddressBulkDeleteRequest;

class UserController extends Controller
{

    public function __construct(UserService $service)
    {
        $this->service = $service;
    }


    public function userList()
    {
        $data = $this->service->getAllUser(false);

        return response()->json([
            'status'  => true,
            'data'   => $data
        ], $data->isEmpty() ? 204 : 200);
    }



   public function userCreate(UserRegistrationRequest $request)
   {
       if((new AuthService())->register($request, 0))
       {
           return response()->json([
               'status'  => true,
           ], 201);
       } else {
           return response()->json([
               'status'  => false,
               'errors'  => ['Something went wrong.']
           ], 500);
       }

   }


    function userDetail($id)
    {
        $result = Cache::remember('userDetail'.$id, 24*60*60*7, function () use ($id) {
            return $this->service->show($id, false);
        });

        if($result && !is_null($result['shop_branch_id']))
        {
            return response()->json([
                'status' => true,
                'errors' => ['No Customer Found.'],
            ], 404);
        }

        return response()->json([
            'status' => true,
            'data'   => $result,
        ], is_null($result) ? 204 : 200);
    }


    public function userOrder($id)
    {
        $data = Cache::remember('userOrders'.$id, 24*60*60*60, function () use ($id) {
            return $this->service->getOrders($id);
        });

        return response()->json([
            'status'    => true,
            'data'      => $data
        ], count($data)==0 ? 204 : 200);

    }


    public function userUpdate(UserProfileUpdateRequest $request, $id)
    {
        $this->service->update($request, $id, false, false);

        return response()->json([
            'status' => true,
        ]);
    }


    public function userAvatarUpdate(AvatarUpdateRequest $request, $id)
    {
        $this->service->updateAvatar($request, $id, false);

        return response()->json([
            'status' => true,
        ]);
    }


    public function userDelete($id)
    {
        if($this->service->deleteCustomer($id))
        {
            return response()->json([
                'status' => true,
            ]);
        }
        return response()->json([
            'status' => false,
            'errors' => ['Selected user is invalid.']
        ], 400);
    }


    public function userAddressList($id)
    {
        $data = Cache::remember('userAddresses'.$id, 24*60*60*7, function() use ($id) {
            return $this->service->getUserAddress($id);
        });

        return response()->json([
            'status'    => true,
            'data'      => $data
        ], count($data)==0 ? 204 : 200);
    }


    public function userAddressCreate(UserAddressCreateRequest $request,$id)
    {
        $this->service->storeAddress($request, $id);

        return response()->json([
            'status' => true,
        ], 201);
    }


    public function userAddressUpdate(UserAddressCreateRequest $request, $id)
    {
        if ($this->service->updateAddress($request, $id, 1)) {
            return response()->json([
                'status' => true,
            ]);
        }
        return response()->json([
            'status' => false,
            'errors' => ['Something went wrong.']
        ], 500);
    }


    public function userAddressDelete($id)
    {
        $status = $this->service->deleteAddress($id);

        if($status == 3)
        {
            return response()->json(['status' => true]);
        } else {
            return response()->json([
                'status' => false,
                'errors' => ['Default address can not be deleted.']
            ], 400);
        }
    }


    public function makeDefaultAddress($id)
    {
        $this->service->makeAddressDefault($id);

        return response()->json([
            'status'        => true,
        ]);
    }


    public function userOrderReport(DateRequest $request, $id)
    {
        $data = $this->service->orderReport($request, $id);

        return response()->json([
            'status' => true,
            'data'   => $data
        ], count($data)==0 ? 204 : 200);
    }

    public function bulkDelete(UserBulkDeleteRequest $request)
    {
        $this->service->deleteCustomers($request);

        return response()->json([
            'status' => true,
        ]);
    }


    public function addressBulkDelete(UserAddressBulkDeleteRequest $request)
    {
        $this->service->deleteAddresses($request);

        return response()->json([
            'status' => true,
        ]);
    }
}
