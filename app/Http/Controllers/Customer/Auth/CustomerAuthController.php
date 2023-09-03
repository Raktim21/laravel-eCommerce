<?php

namespace App\Http\Controllers\Customer\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\AuthRequest;
use App\Http\Requests\ConfirmPasswordRequest;
use App\Http\Requests\ResetPasswordRequest;
use App\Http\Requests\UserRegistrationRequest;
use App\Http\Services\AuthService;
use App\Mail\EmailVerificationMail;
use App\Models\CustomerCart;
use App\Models\EmailVerification;
use App\Models\EmailVerificationCode;
use App\Models\Order;
use App\Models\ProductReview;
use App\Models\User;
use App\Models\UserAddress;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Token;


class CustomerAuthController extends Controller
{
    protected $service;

    public function __construct(AuthService $service)
    {
        $this->service = $service;
    }


    public function register(UserRegistrationRequest $request): \Illuminate\Http\JsonResponse
    {
        if($this->service->register($request, 1)) {
            return response()->json([
                'status'  => true,
            ], 201);
        } else {
            return response()->json([
                'status'  => false,
            ], 500);
        }
    }


    public function login(AuthRequest $request)
    {
        return $this->service->login($request, 0);
    }


    public function me()
    {
        $data = Cache::remember('customer_auth_profile'.auth()->user()->id, 24*60*60, function () {
            return array(
                'user_data' => $this->service->profile(),
                'other'     => array(
                    'order_count'             => Order::where('user_id', auth()->guard('user-api')->user()->id)->count(),
                    'completed_order_count'   => Order::where('user_id', auth()->guard('user-api')->user()->id)
                        ->where('order_status_id', 4)->count(),
                    'shipping_address_count'  => UserAddress::where('user_id', auth()->guard('user-api')->user()->id)->count(),
                ));
        });

        return response()->json([
            'status'    => true,
            'data'      => $data,
        ]);
    }



    public function logout()
    {
        $id = auth()->user()->id;

        if($this->service->logout(request()->cookie('customer_refresh_token')))
        {
            Cache::delete('customer_auth_profile'.$id);
            return response()->json([
                    'status' => true,
                ])->cookie('customer_refresh_token',null,43200,null,null,true,true);
        }
        return response()->json([
            'status'  => false,
            'error'   => 'Unauthorized'
        ],401);
    }



    public function refresh()
    {
        return $this->service->refresh(0, request()->cookie('customer_refresh_token'));
    }

    public function resetPassword(ResetPasswordRequest $request)
    {
        $token = $this->service->resetPWD($request, 0);

        if($token==null)
        {
            return response()->json([
                'status'        => false,
                'errors'        => ['Mail server has not been configured yet.']
            ], 400);
        }

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

    public function deleteAccount(): \Illuminate\Http\JsonResponse
    {
        $id = auth()->user()->id;

        $status = $this->service->deleteAccount(request()->cookie('customer_refresh_token'));

        if($status == 1)
        {
            Cache::delete('customer_auth_profile'.$id);
            return response()->json([
                'status' => true,
            ])->cookie('customer_refresh_token',null,43200,null,null,true,true);
        }
        else if($status == 0)
        {
            return response()->json([
                'status' => false,
                'errors' => ['You cannot deactivate your account until your pending orders are completed.']
            ], 400);
        }
        return response()->json([
            'status' => false,
            'errors' => ['Unauthorized user.']
        ], 401);
    }


    public function sendVerificationCode(): \Illuminate\Http\JsonResponse
    {
        if (auth()->guard('user-api')->user()->email_verified_at)
        {
            return response()->json([
                'status' => false,
                'errors' => ['You have already verified your email.']
            ], 400);
        }

        $code = EmailVerification::where('user_id', auth()->guard('user-api')->user()->id)->first();

        $new_code = rand(100000, 999999);

        if ($code) {
            if (Carbon::now()->diffInMinutes($code->created_at) < 10 || Carbon::now()->diffInMinutes($code->updated_at) < 10)
            {
                return response()->json([
                    'status' => false,
                    'errors' => ['Please wait 10 minutes to send another code.']
                ], 400);
            }

            $code->update([
                'verification_token' => Hash::make($new_code),
                'expired_at'         => Carbon::now()->addMonth()
            ]);
        } else {
            EmailVerification::create([
                'user_id'               => auth()->guard('user-api')->user()->id,
                'verification_token'    => Hash::make($new_code),
                'expired_at'            => Carbon::now()->addMonth()
            ]);
        }

        try {
            Mail::to(auth()->guard('user-api')->user()->username)->queue(new EmailVerificationMail(auth()->user(), $new_code));
        } catch (\Throwable $th) {}

        return response()->json([
            'status'  => true,
        ]);
    }


    public function emailVerification(Request $request): \Illuminate\Http\JsonResponse
    {
        $validated = Validator::make($request->all(), [
            'code' => 'required|numeric',
        ]);

        if ($validated->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validated->errors()->all()
            ], 422);
        }

        $code = EmailVerification::where('user_id', auth()->guard('user-api')->user()->id)->first();

        if ($code && Hash::check($request->code, $code->verification_token) && $code->expired_at >= Carbon::now('Asia/Dhaka'))
        {
            $code->user->email_verified_at = Carbon::now();
            $code->user->save();
            $code->delete();

            Cache::delete('customer_auth_profile'.auth()->user()->id);

            return response()->json([
                'status'  => true,
            ]);
        }
        return response()->json([
            'status' => false,
            'errors' => ['Invalid verification code.']
        ], 400);
    }
}
