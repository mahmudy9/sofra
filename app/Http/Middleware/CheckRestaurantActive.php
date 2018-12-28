<?php

namespace App\Http\Middleware;

use Closure;

class CheckRestaurantActive
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
        $rest = auth('api_rest')->user();
        if($rest->is_activated == false)
        {
            return apiRes(400 , 'Exceeded max app fees amount allowed ,Cant perform actions till pay the App Fees');
        }
        return $next($request);
    }
}
