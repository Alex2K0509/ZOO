<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\LOGS;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

use Illuminate\Support\Facades\Date;
class RegisterLogs
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

            return $next($request);

    }
    /**
     * @param Request $request
     * @param Response $response
     */
    public function terminate($request,$response){
       // $endTime    = microtime(true);
        //$duration   = $endTime - LARAVEL_START;
        //$ftime      = date('Y-m-d H:i:s', LARAVEL_START);
        LOGS::create([
            'ip' => $request->ip(),
            'log_host' =>$request->ip(),
            'log_time' =>date('Y-m-d H:i:s', LARAVEL_START),
            'log_time_date' => date('Y-m-d H:i:s', LARAVEL_START),
            'log_headers' => json_encode($request->headers->all()),
            'log_url' => $request->getRequestUri(),
            'log_method' => $request->method(),
            'log_request' => $request->getContent(),
            'log_response' =>  $response->getContent(),
            'log_response_status' => $response->getStatusCode(),
            'log_user_id' => (auth()->user() ? auth()->user()->id   : DB::raw('NULL')),
            'log_user_name' =>  (auth()->user() ? auth()->user()->name : DB::raw('NULL')),
        ]);
    }
}
