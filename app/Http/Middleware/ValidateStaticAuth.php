<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ValidateStaticAuth
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->header('Authorization') != 'oRaNquwLewtLNAOUwurLDqAfEAdxCeEDkfTwVJudjLOtPMWYUGMmJMnxNOlkfgmK') {
            return response()->json([
                'status' => false,
                'errors' => ['Unauthorized'],
            ],401);
        }

        return $next($request);
    }
}
