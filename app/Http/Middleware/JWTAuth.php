<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Facades\JWTAuth as FacadesJWTAuth;
use Tymon\JWTAuth\Http\Middleware\BaseMiddleware;
use Tymon\JWTAuth\Http\Parser\AuthHeaders;

class JWTAuth
{
    public function handle(Request $request, Closure $next, $guard = null)
    {
        if ($guard != null) {
            \Config::set('auth.defaults.guard',$guard);
            auth()->shouldUse($guard);
        }

        // dd('asi');
        // $token_name = $guard == 'admin-api' ? 'admin_refresh_token' : 'customer_refresh_token';

        $token = request()->header('Authorization');
        

        if (!$token) {
            return response()->json( [
                'errors'   => 'Unauthenticated',
                'message'  => 'Unauthenticated'
            ],401 );
        }

        $token = explode(' ', $token);

        if (count($token) < 2) {
            return response()->json( [
                'errors'   => 'Unauthenticated',
                'message'  => 'Unauthenticated'
            ],401 );
        }
        
        $token = $token[1];

        if (!auth()->check()) {
            try {
                $forever = true;
                FacadesJWTAuth::parseToken()->invalidate($forever);

                return response()->json( [
                    'errors'   => 'Unauthenticated',
                    'message'  => 'Unauthenticated'
                ],401 );


            } catch ( TokenExpiredException $exception ) {
                return response()->json( [
                    'errors'   => 'Unauthenticated',
                    'message'  =>'Unauthenticated'

                ], 401);


            } catch ( TokenInvalidException $exception ) {
                return response()->json( [
                    'errors'   => 'Unauthenticated',
                    'message'  =>'Unauthenticated'
                ], 401);


            } catch ( JWTException $exception ) {
                return response()->json( [
                    'errors'   => 'Unauthenticated',
                    'message'  =>'Unauthenticated'
                ], 401);

            }

        }



        try {

            $payload = FacadesJWTAuth::manager()->getJWTProvider()->decode($token);
            
            if (array_key_exists("refresh_token",$payload) && $payload['refresh_token'] == true) {

                
                return response()->json( [
                    'errors'   => 'Unauthenticated',
                    'message'  =>'Unauthenticated'
                ],401 );
            }
        } catch (\Throwable $th) {

            return response()->json( [
                'errors'   => 'Unauthenticated',
                'message'  =>'Unauthenticated'
            ],401 );
        }

        // ->cookie(
        //     $token_name,
        //     null,
        //     null,
        //     null,
        //     true,
        //     true
        // )
        return $next($request);


    }
}
