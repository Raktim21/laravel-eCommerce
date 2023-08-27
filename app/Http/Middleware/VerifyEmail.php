<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class VerifyEmail
{
    public function handle(Request $request, Closure $next)
    {
        if(auth()->guard('user-api')->check() && is_null(auth()->user()->email_verified_at))
        {
            return response()->json([
                'status'    => true,
                'errors'    => ['Please verify your email first.']
            ], 400);
        }
        return $next($request);
    }
}
