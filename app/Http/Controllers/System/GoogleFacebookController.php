<?php

namespace App\Http\Controllers\System;

use App\Models\CustomerCart;
use App\Models\GeneralSetting;
use App\Models\User;
use App\Models\UserProfile;
use Carbon\Carbon;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Laravel\Socialite\Facades\Socialite;
use Tymon\JWTAuth\Facades\JWTAuth;

class GoogleFacebookController extends Controller
{
    private string $base_url = 'https://graph.facebook.com/v16.0/';

    private string $secret = '4080b82c1b9e815bfe96f5e92bee53fe';

    private string $app_id = '950332889409684';

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
        if(Cache::get('admin_route'))
        {
            return $this->handleAdminCallback();
        }
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
        if ($token = auth()->attempt($credentials)) {

            $auth_user = auth()->user();

            $auth_user->update(['is_active' => 1, 'last_login' => Carbon::now()]);

            $expiration = Carbon::now()->addMonth();

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
                    'user' => $auth_user,
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

    public function redirectAdmin()
    {
        Cache::clear();
        $scopes = [
            'email',
            'pages_show_list',
            'pages_manage_posts',
            'pages_manage_engagement',
            'pages_messaging',
            'pages_read_engagement',
            'pages_manage_metadata',
            'pages_read_user_content'
        ];

        Cache::remember('admin_route', 60*2, function () {
            return true;
        });

        return Socialite::driver('facebook')
            ->scopes($scopes)
            ->redirect();
    }

    public function handleAdminCallback()
    {
        try {
            $user = Socialite::driver('facebook')->user();

            $user_access_token = $this->getTokenResponse($user->token);

            Log::info($user_access_token);

            $page_list = $this->getPages($user_access_token);

            $pages = array_filter($page_list['data'], function ($item) {
                return isset($item['access_token']);
            });

            Log::info($page_list);

            $pages = array_values($pages);

            if(count($pages) > 1)
            {
                return response()->json([
                    'status'    => false,
                    'errors'    => ['You must select only one facebook page to connect.']
                ], 422);
            }

            $this->setUpWebhook($pages[0]['id'], $pages[0]['access_token']);

            $payload = array(
                'page_api_domain'   => env('APP_URL') ?? 'https://example.com/admin-panel',
                'page_access_token' => $pages[0]['access_token'],
                'page_api_token'    => env('API_ACCESS_TOKEN') ?? 'oRaNquwLewtLNAOUwurLDqAfEAdxCeEDkfTwVJudjLOtPMWYUGMmJMnxNOlkfgmK',
                'page_id'           => $pages[0]['id'],
                'page_site_domain'  => env('FRONTEND_URL') ?? 'https://example.com',
                'page_name'         => $pages[0]['name'],
            );

            sendMessengerResponse($payload, 'create_page_info');

            GeneralSetting::first()->update([
                'facebook_page_id'  => $pages[0]['id'],
            ]);

            return response()->json([
                'status' => true,
                'payload' => $payload
            ]);
        }
        catch (\Throwable $th)
        {
            return response()->json([
                'status'    => false,
                'errors'    => [$th->getMessage()]
            ], 400);
        }
    }

    private function getTokenResponse($token)
    {
        $user_token_url = $this->base_url.'oauth/access_token?grant_type=fb_exchange_token&client_id='. $this->app_id .'&client_secret='. $this->secret .'&fb_exchange_token='.$token;

        $ch = curl_init($user_token_url);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);

        $response = json_decode($response, true);

        return $response['access_token'];
    }

    private function getPages($access_token)
    {
        $page_list_url = $this->base_url.'me/accounts?fields=id,name,access_token&access_token='.$access_token;

        $ch = curl_init($page_list_url);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);

        return json_decode($response, true);
    }

    private function setUpWebhook($page_id, $access_token)
    {
        $payload1 = array(
            'subscribed_fields' => ["messages","messaging_feedback", "messaging_postbacks", "message_deliveries",
                "messaging_referrals", "standby", "messaging_customer_information",
                "messaging_optins"]
        );

        $this->sendRequest($this->base_url.$page_id.'/subscribed_apps', $payload1);

        $payload2 = array(
            'get_started' => array(
                'payload'   => 'GET STARTED'
            ),
            'whitelisted_domains' => [
                'https://chatbotapi.selopian.us/', 'https://chatbotapi.selopian.us/views/create_account',
                'https://chatbotapi.selopian.us/views/cart', 'https://chatbotapi.selopian.us/views/success_page',
                'https://chatbotapi.selopian.us/views/track_order_page', 'https://chatbotapi.selopian.us/views/subscriptions',
                'https://chatbotapi.selopian.us/views/account'
            ]
        );

        $this->sendRequest($this->base_url.'me/messenger_profile?access_token='.$access_token, $payload2);
    }

    private function sendRequest($route, $payload)
    {
        $ch = curl_init($route);

        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);

        curl_exec($ch);
        curl_close($ch);
    }
}
