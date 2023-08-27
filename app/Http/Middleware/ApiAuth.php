<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ApiAuth
{
    public function handle(Request $request, Closure $next)
    {
       if($request->header('Authorization') != env('API_ACCESS_TOKEN')){
            return response()->json([
                'status' => false,
                'errors' => ['Unauthorized'],
            ],401);
        }
        return $next($request);
    }
}
