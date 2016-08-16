<?php

namespace App\Http\Middleware;

use App\Setup;
use Carbon\Carbon;
use Closure;
use Illuminate\Support\Facades\Auth;

class SetupMiddleware
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
        $userId=Auth::user()->id;

       $setup = Setup::select()->where('user_id','=',$userId)->first();
        if(count($setup)>0){

        }else{
            $created = new Carbon(Auth::user()->created_at);
            $now = Carbon::now();
            if ($created->diff($now)->days > 30) {
                return redirect('renewal');
            }
        }
        return $next($request);
    }

}
