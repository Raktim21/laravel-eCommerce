<?php

namespace App\Http\Controllers\Admin;

use App\Http\Services\UserService;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use App\Http\Requests\AvatarUpdateRequest;
use App\Http\Requests\PasswordUpdateRequest;
use App\Http\Requests\UserProfileUpdateRequest;

class ProfileController extends Controller
{
    public function __construct(UserService $service)
    {
        $this->service  = $service;
    }

    public function permissions()
    {
        $data = Cache::remember('permissions'.auth()->user()->id, 60*60*24*7, function () {
            return $this->service->getAuthPermissions();
        });

        return response()->json([
            'status'    => true,
            'data'      => $data
        ], count($data)==0 ? 204 : 200);
    }

    public function profileUpdate(UserProfileUpdateRequest $request)
    {
        $this->service->update($request, auth()->guard('user-api')->user()->id, true, true);

        Cache::delete('adminAuthProfile'.auth()->user()->id);

        return response()->json(['status' => true]);
    }

    public function avatarUpdate(AvatarUpdateRequest $request)
    {
        $this->service->updateAvatar($request, auth()->guard('admin-api')->user()->id, true);

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
}
