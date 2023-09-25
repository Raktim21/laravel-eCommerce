<?php

namespace App\Http\Controllers\Customer;

use App\Http\Services\UserService;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use App\Http\Requests\AvatarUpdateRequest;
use App\Http\Requests\PasswordUpdateRequest;
use App\Http\Requests\UserAddressCreateRequest;
use App\Http\Requests\UserProfileUpdateRequest;

class ProfileController extends Controller
{
    protected $service;

    public function __construct(UserService $service)
    {
        $this->service = $service;
    }



    public function profileUpdate(UserProfileUpdateRequest $request)
    {
        $this->service->update($request, auth()->guard('user-api')->user()->id, true, false);

        return response()->json(['status' => true]);
    }



    public function avatarUpdate(AvatarUpdateRequest $request)
    {
        $this->service->updateAvatar($request, auth()->guard('user-api')->user()->id, false);

        return response()->json(['status' => true]);
    }



    public function passwordUpdate(PasswordUpdateRequest $request)
    {
        if($this->service->updatePassword($request)) {
            return response()->json(['status' => true]);
        }
        return response()->json([
            'status' => false,
            'errors' => ['Old password does not match.'],
        ], 400);
    }



    public function addressList()
    {
        $data = Cache::remember('customer_addresses'.auth()->user()->id, 24*60*60*7, function () {
            return $this->service->getUserAddress(auth()->guard('user-api')->user()->id);
        });

        return response()->json([
            'status' => true,
            'data'   => $data
        ], count($data)==0 ? 204 : 200);
    }



    public function createNewAddress(UserAddressCreateRequest $request)
    {
        $this->service->storeAddress($request, auth()->guard('user-api')->user()->id);

        return response()->json([
            'status' => true,
        ], 201);
    }


    public function updateAddress(UserAddressCreateRequest $request, $id)
    {
        if ($this->service->updateAddress($request, $id, 0))
        {
            return response()->json(['status'  => true]);
        } else {
            return response()->json([
                'status'    => false,
                'errors'    => ['You can not update this user address.']
            ], 403);
        }
    }



    public function deleteAddress($id)
    {
        $status = $this->service->deleteAddress($id);

        if ($status == 1)
        {
            return response()->json([
                'status'  => false,
                'errors' => ['You can not delete this user address.']
            ], 403);
        }
        else if ($status == 2)
        {
            return response()->json([
                'status'  => false,
                'errors' => ['Default user address can not be deleted.']
            ], 400);
        }
        else
        {
            return response()->json([
                'status'  => true,
            ]);
        }
    }



    public function makeDefaultAddress($id)
    {
        if ($this->service->makeAddressDefault($id))
        {
            return response()->json(['status'  => true]);
        }
        return response()->json([
            'status'  => false,
            'errors' => ['You can not update this user address.']
        ], 403);
    }
}
