<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Session;

class ActiveBand
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

        if (!Session::has('activeBandId'))
        {
            return redirect('band');
        }

        return $next($request);
    }

}
