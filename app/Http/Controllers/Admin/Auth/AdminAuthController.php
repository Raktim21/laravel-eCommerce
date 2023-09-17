<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\AuthRequest;
use App\Http\Requests\ConfirmPasswordRequest;
use App\Http\Requests\ResetPasswordRequest;
use App\Http\Services\AuthService;
use Illuminate\Support\Facades\Cache;

class AdminAuthController extends Controller
{
    protected $service;

    public function __construct(AuthService $service)
    {
        \Config::set('auth.defaults.guard','admin-api');

        $this->service = $service;
    }

    public function login(AuthRequest $request)
    {
        return $this->service->login($request, 1);
    }


    public function me()
    {
        $data = Cache::remember('adminAuthProfile'.auth()->user()->id, 60*60*24, function () {
            return $this->service->profile();
        });

        return response()->json([
            'status'    => true,
            'data'      => $data,
        ], is_null($data) ? 204 : 200);
    }


    public function refresh()
    {
        return $this->service->refresh(1, request()->cookie('admin_refresh_token'));
    }


    public function resetPassword(ResetPasswordRequest $request)
    {
        $token = $this->service->resetPWD($request, 1);

        return response()->json([
            'status'    => true,
            'data'      => array('reset_token' => $token)
        ]);
    }


    public function confirmPassword(ConfirmPasswordRequest $request)
    {
        $this->service->confirmPWD($request);

        return response()->json([
            'status' => true,
        ]);
    }


    public function logout()
    {
        $id = auth()->user()->id;
        if($this->service->logout(request()->cookie('admin_refresh_token')))
        {
            Cache::delete('adminAuthProfile'.$id);
            return response()->json([
                'status'    => true,
            ])->cookie('admin_refresh_token', null, 43200, null, null, true, true );
        }

        return response()->json([
            'status'  => false,
            'errors'  => ['Unauthorized']
        ],401);
    }
}
