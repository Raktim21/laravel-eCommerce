<?php

namespace App\Http\Services;

use App\Mail\EmailVerificationMail;
use App\Mail\PasswordResetMail;
use App\Models\CustomerCart;
use App\Models\EmailVerification;
use App\Models\Order;
use App\Models\User;
use App\Models\UserProfile;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Token;
use Mews\Captcha\Facades\Captcha;

class AuthService
{
    public function register(Request $request, $guardChecked): bool
    {
        DB::beginTransaction();

        try{
            $user = User::create([
                'name'              => $request->name,
                'username'          => $request->username,
                'password'          => Hash::make($request['password']),
                'phone'             => $request->phone,
                'phone_verified_at' => Carbon::now(),
            ]);

            if($guardChecked == 1)
            {
                $user->assignRole(3);
            } else {
                \Config::set('auth.defaults.guard','user-api');
                $user->assignRole(3);
                \Config::set('auth.defaults.guard','admin-api');
            }

            $profile = UserProfile::create([
                'user_id'           => $user->id,
                'user_sex_id'       => $request->gender,
            ]);

            if ($request->hasFile('avatar')) {
                saveImage($request->file('avatar'), '/uploads/customer/avatars/', $profile, 'image');
            }

            DB::commit();
            return true;
        }
        catch (QueryException $e)
        {
            DB::rollback();
            return false;
        }
    }

    public function login(Request $request, $isAdmin)
    {
        $credentials = array(
            (filter_var($request->get('username'), FILTER_VALIDATE_EMAIL) ? 'username' : 'phone') => $request->get('username'),
            'password' => $request->get('password')
        );

        if ($this->hasTooManyLoginAttempts($request)) {

            if ($this->incrementLoginAttempts($request) == 5) {
                return response()->json([
                    'status'  => false,
                    'errors'  => ['Too many request. Please fill up the captcha.'],
                    'captcha' => Captcha::create('default',true),
                ], 400);
            }

            $validator = Validator::make($request->all(), [
                'captcha' => 'required|captcha_api:'. request('key') . ',default'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status'  => false,
                    'errors'  => ['Invalid captcha'],
                    'captcha' => Captcha::create('default',true),
                ],422);
            }
        }

        if ($token = auth()->attempt($credentials)) {

            $user = auth()->user();

            $expiration   = Carbon::now()->addMonth(1);

            $refreshToken = JWTAuth::customClaims(['exp' => $expiration->timestamp , 'refresh_token' => true])->fromUser($user);

            $this->clearLoginAttempts($request);

            if ($isAdmin === 1) {
                $data = array(
                            'user'  =>  $user,
                            'token' =>  array(
                                            'admin_access_token' => $token,
                                            'token_type'     => 'bearer',
                                            'expires_in'     => auth()->factory()->getTTL() * 60
                                        )
                        );
            } else {

                $cart = false;
                if (request()->cookie('customer_unique_token')) {
                    if (CustomerCart::where('guest_session_id', request()->cookie('customer_unique_token'))->count() > 0) {
                        $cart = true;
                    }
                }

                $data = array(
                            'user'  =>  $user,
                            'token' =>  array(
                                            'customer_access_token' => $token,
                                            'token_type'     => 'bearer',
                                            'expires_in'     => auth()->factory()->getTTL() * 60
                                        ),
                            'cart'  =>  $cart,
                        );
            }

            auth()->user()->update([
                'is_active'  => 1,
                'last_login' => Carbon::now()
            ]);

            return response()->json([
                'status'         => true,
                'data'           => $data
            ])->cookie(
                $isAdmin === 1 ? 'admin_refresh_token' : 'customer_refresh_token',
                $refreshToken,
                43200,
                null,
                null,
                true,
                true
            );

        } else {
            $this->incrementLoginAttempts($request);

            return response()->json([
                'status' => false,
                'errors' => ['Unauthorized user.']
            ], 401);
        }
    }


    protected function hasTooManyLoginAttempts(Request $request)
    {
        return $this->limiter()->tooManyAttempts($this->throttleKey($request), 4, 2 * 60);
    }


    protected function incrementLoginAttempts(Request $request)
    {
        return $this->limiter()->hit(
                    $this->throttleKey($request),
                    2 * 60
                );
    }


    protected function clearLoginAttempts(Request $request): void
    {
        $this->limiter()->clear($this->throttleKey($request));
    }


    protected function throttleKey(Request $request): string
    {
        return Str::lower($request->input('email')) . '|' . $request->ip();
    }


    protected function limiter()
    {
        return app(\Illuminate\Cache\RateLimiter::class);
    }


    public function profile()
    {
        return User::with('profile.gender')->with('roles')->find(auth()->user()->id);
    }

    public function logout($refresh_token): bool
    {
        try {
            auth()->logout();

            JWTAuth::manager()->invalidate(new Token($refresh_token), true);

            return true;

        } catch (\Throwable $th) {
            return false;
        }
    }

    public function refresh($isAdmin,$refresh_token)
    {
        if ($refresh_token) {

            $payload = JWTAuth::manager()->getJWTProvider()->decode($refresh_token);

            if (array_key_exists("refresh_token", $payload) && $payload['refresh_token']) {
                $user = JWTAuth::setToken($refresh_token)->toUser();
                if ($user) {
                    return response()->json([
                        $isAdmin === 1 ? 'admin_access_token' : 'customer_access_token' =>  JWTAuth::fromUser($user),
                        'token_type' => 'bearer',
                        'expires_in' => auth()->factory()->getTTL() * 60
                    ]);
                }
            }

        }

        return response()->json([
            'status'  => false,
            'error'   => 'Unauthorized'
        ], 401);
    }

    public function resetPWD(Request $request, $isAdmin)
    {
        $user = User::where('username', $request->username)->first();

        $code = Str::random($isAdmin==1 ? 5 : 6);
        $token = Hash::make($code);

        $user->update([
            'password_reset_token' => $token,
            'password_reset_code'  => $code,
        ]);

        try{
            $this->notifyUser($user, $code);

            return $token;
        } catch (\Exception $e)
        {
            return null;
        }
    }

    public function confirmPWD(Request $request): void
    {
        $user = User::where('password_reset_token', $request->token)
            ->where('password_reset_code',$request->code)->first();

        $user->update([
            'password'              => Hash::make($request->password),
            'password_reset_token'  => null,
            'password_reset_code'   => null,
        ]);
    }

    public function deleteAccount($token): int
    {
        $user = auth()->user()->id;

        if(Order::where('user_id', $user)
            ->whereIn('order_status_id', [1,2])->exists())
        {
            return 0;
        }

        if($this->logout($token))
        {
            User::find($user)->update(['is_active' => 0]);

            return 1;
        }
        return 2;
    }

    private function notifyUser($user, $code): void
    {
        try {
            $to = $user->username;

            $data = [
                'user' => $user->name,
                'code' => $code
            ];

            Mail::to($to)->send(new PasswordResetMail($data));
        } catch (\Throwable $th) {}
    }
}
