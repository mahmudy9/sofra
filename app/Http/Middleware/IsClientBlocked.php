<?php

namespace App\Http\Middleware;

use Closure;

class IsClientBlocked
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if($request->user()->activated != 1)
        {
            return apiRes(401 , 'unauthorized to do this action');
        }
        return $next($request);
    }
}
