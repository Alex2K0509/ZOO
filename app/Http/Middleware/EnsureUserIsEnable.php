<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class EnsureUserIsEnable
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

        $status = auth()->user()->status;
            if($status != 1){
                Session::flush();
                return  redirect('/login')->with('error','Tu cuenta se encuentra bloqueada, habla con un administrador');
            }

        return $next($request);
    }
}
