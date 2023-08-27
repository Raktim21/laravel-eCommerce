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
        $data = array(
            'user_data' => $this->service->profile(),
            'other'     => array(
                'order_count'             => Order::where('user_id', auth()->guard('user-api')->user()->id)->count(),
                'completed_order_count'   => Order::where('user_id', auth()->guard('user-api')->user()->id)
                    ->where('order_status_id', 4)->count(),
                'shipping_address_count'  => UserAddress::where('user_id', auth()->guard('user-api')->user()->id)->count(),
            ));

        return response()->json([
            'status'    => true,
            'data'      => $data,
        ]);
    }



    public function logout()
    {
        if($this->service->logout(request()->cookie('customer_refresh_token')))
        {
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
        if($this->service->deleteAccount(request()->cookie('customer_refresh_token')))
        {
            return response()->json([
                'status' => true,
            ])->cookie('customer_refresh_token',null,43200,null,null,true,true);
        }
        return response()->json(['status' => false, 'errors' => ['Unauthorized User']]);
    }


//    no use

    public function sendVerificationCode()
    {
        if (auth()->user()->email_verified_at) {
            return response()->json([
                'error' => 'Email already verified'
            ], 400);
        }

        $code = EmailVerificationCode::where('user_id', auth()->guard('user-api')->user()->id)->first();

        if ($code) {
            if (Carbon::now()->diffInMinutes($code->created_at) < 2) {

                return response()->json([
                    'error' => 'Please wait 2 minute to send another code'
                ], 400);
            }
        }

        $user = User::find(auth()->guard('user-api')->user()->id);


        // $user->emailVerificationCode()->create([
        //     'user_id'    => $user->id,
        //     'code'       => rand(100000, 999999),
        //     'created_at' => Carbon::now(),
        // ]);

        $email_verification = new EmailVerificationCode();
        $email_verification->user_id = $user->id;
        $email_verification->code = rand(100000, 999999);
        $email_verification->save();

        Mail::to($user->username)->queue(new EmailVerificationMail($user, $email_verification->code));

        return response()->json([
            'status'  => true,
        ]);

    }


//    no use
    public function emailVerification(Request $request)
    {

        $validated = Validator::make($request->all(), [
            'code' => 'required|numeric',
        ]);

        if ($validated->fails()) {
            return response()->json(['error' => $validated->errors()], 400);
        }


        $emailVerificationCode = EmailVerificationCode::where(auth()->guard('user-api')->user()->id)->where('code', $request->code)->first();

        if ($emailVerificationCode) {

            $user = User::find($emailVerificationCode->user_id);
            $user->email_verified_at = Carbon::now();
            $user->save();

            $emailVerificationCode->delete();

            return response()->json([
                'status'  => true,
            ]);

        }else {
            return response()->json([
                'status'  => false,
            ]);
        }
    }
}
