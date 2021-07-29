<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class IsAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Auth::user(), apakah dia login
        // Auth::user()->roles, check roles users di database

        if(Auth::user() && Auth::user()->roles == "ADMIN") {
            return $next($request);
        }

        return redirect()->route('index');
    }
}
