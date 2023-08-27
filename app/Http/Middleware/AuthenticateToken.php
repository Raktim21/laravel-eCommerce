<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AuthenticateToken
{

    public function handle(Request $request, Closure $next)
    {
        if ($request->header('Authorization') != 's2ys10sav175HakOg7FZeTvKb0a1e6wMjGOfSCgBDEsv99wRjWs6NZyWXEI6') {
            return response()->json('This key is unauthorized', 401);
        }
        return $next($request);
    }
}
