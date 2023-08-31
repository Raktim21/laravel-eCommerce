<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\AvatarUpdateRequest;
use App\Http\Requests\PasswordUpdateRequest;
use App\Http\Requests\UserProfileUpdateRequest;
use App\Http\Services\UserService;
use Illuminate\Support\Facades\Cache;

class ProfileController extends Controller
{
    public function __construct(UserService $service)
    {
        $this->service  = $service;
    }

    public function permissions(): \Illuminate\Http\JsonResponse
    {
        $data = Cache::remember('authPermissions', 60*60*24, function () {
            return $this->service->getAuthPermissions();
        });

        return response()->json([
            'status'    => true,
            'data'      => $data
        ], count($data)==0 ? 204 : 200);
    }

    public function profileUpdate(UserProfileUpdateRequest $request): \Illuminate\Http\JsonResponse
    {
        $this->service->update($request, auth()->guard('user-api')->user()->id, true, true);

        Cache::delete('adminAuthProfile');

        return response()->json(['status' => true]);
    }

    public function avatarUpdate(AvatarUpdateRequest $request): \Illuminate\Http\JsonResponse
    {
        $this->service->updateAvatar($request, auth()->guard('admin-api')->user()->id, true);

        Cache::delete('adminAuthProfile');

        return response()->json(['status' => true]);
    }


    public function passwordUpdate(PasswordUpdateRequest $request): \Illuminate\Http\JsonResponse
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
