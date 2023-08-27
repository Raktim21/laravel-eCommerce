<?php

namespace App\Http\Controllers\System;

use App\Models\CustomerCart;
use App\Models\User;
use App\Models\UserProfile;
use Carbon\Carbon;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Laravel\Socialite\Facades\Socialite;
use Tymon\JWTAuth\Facades\JWTAuth;

class GoogleFacebookController extends Controller
{
    public function redirect(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'auth_type'     => 'required|in:SignUp,SignIn',
            'driver'        => 'required|in:facebook,google'
        ]);

        if ($validate->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validate->errors()->all()
            ], 422);
        }
        Cache::remember('auth_type', 60*2, function ()
        {
            return \request()->input('auth_type');
        });

        return Socialite::driver($request->driver)->redirect();
    }

    public function handleGoogleCallback()
    {
        try
        {
            $user = Socialite::driver('google')->user();

            if (Cache::get('auth_type') == 'SignUp')
            {
                Cache::delete('auth_type');

                $validate = Validator::make((array)$user, [
                    'name'      => 'unique:users,name',
                    'email'     => 'unique:users,username',
                    'id'        => 'unique:users,google_id'
                ]);

                if ($validate->fails()) {
                    return response()->json([
                        'status' => false,
                        'errors' => $validate->errors()->all()
                    ], 422);
                }

                $this->createUser($user, 1);

                return response()->json(['status' => true], 201);
            } else {
                Cache::delete('auth_type');

                $credentials = array(
                    'username'  => $user->email,
                    'password'  => $user->id
                );

                return $this->authorizeUser($credentials);
            }
        } catch (\Exception $e)
        {
            return response()->json([
                'status'    => false,
                'errors'    => [$e->getMessage()]
            ], 400);
        }
    }

    public function handleFacebookCallback()
    {
        try {
            $user = Socialite::driver('facebook')->user();

            $type = Cache::get('auth_type');
            Cache::delete('auth_type');

            if($type == 'SignUp')
            {
                $validate = Validator::make((array)$user, [
                    'name'      => 'unique:users,name',
                    'email'     => 'unique:users,username',
                    'id'        => 'unique:users,facebook_id'
                ]);

                if ($validate->fails()) {
                    return response()->json([
                        'status' => false,
                        'errors' => $user
                    ], 422);
                }

                $this->createUser($user, 2);
                return response()->json(['status' => true], 201);
            } else {
                $credentials = array(
                    'username'  => $user->email,
                    'password'  => $user->id
                );

                return $this->authorizeUser($credentials);
            }

        } catch (\Exception $e)
        {
            return response()->json([
                'status'    => false,
                'errors'    => [$e->getMessage()]
            ], 400);
        }
    }

    private function createUser($user, $idType): void
    {
        $new_user = User::create([
            'name'              => $user->name,
            'username'          => $user->email,
            'google_id'         => $idType==1 ? $user->id : null,
            'facebook_id'       => $idType==2 ? $user->id : null,
            'password'          => Hash::make($user->id),
            'email_verified_at' => Carbon::now(),
            'phone_verified_at' => Carbon::now()
        ]);

        $new_user->assignRole(3);

        UserProfile::create([
            'user_id'       => $new_user->id,
            'image'         => $user->avatar,
            'user_sex_id'   => 3
        ]);
    }

    private function authorizeUser($credentials)
    {
        $user = User::withTrashed()->where('username', $credentials['username'])->first();

        $user?->restore();

        if ($token = auth()->attempt($credentials)) {

            $auth_user = auth()->user();

            $expiration = Carbon::now()->addMonth(1);

            $refreshToken = JWTAuth::customClaims(['exp' => $expiration->timestamp, 'refresh_token' => true])->fromUser($auth_user);

            $cart = false;

            if (request()->cookie('customer_unique_token')) {
                if (CustomerCart::where('guest_session_id', request()->cookie('customer_unique_token'))->count() > 0) {
                    $cart = true;
                }
            }

            return response()->json([
                'status' => true,
                'data' => array(
                    'user' => $user,
                    'token' => array(
                        'customer_access_token' => $token,
                        'token_type' => 'bearer',
                        'expires_in' => auth()->factory()->getTTL() * 60
                    ),
                    'cart' => $cart,
                )
            ])->cookie('customer_refresh_token', $refreshToken, 43200, null, null, true, true);
        }

        return response()->json([
            'status'  => false,
            'errors'  => ['Unauthorized User.']
        ], 401);
    }
}
