<?php

namespace App\Http\Middleware;

use App\Models\User;
use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureTokenIsValid
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
        if (!(Auth::guard('api')->check())) {
                return response()->json(['message' => 'Un Authenticated'], 403);
        }

        // increment age automatically every routing and if months greater than 6 increase year by one

        Auth::user()->age = Carbon::parse(Auth::user()->birth_day)->diff(Carbon::now())->format('%y.%m');
        $pureAge=(int)(Auth::user()->age);
        if(((Auth::user()->age)-$pureAge) >= 0.6){
           foreach (User::all() as $user){
               if($user->id == Auth::id()){
                   $user->age = $pureAge +1 ;
                   $user->save();
               }
           }

        }
        return $next($request);
    }

}
