<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Session;

class WebAdminCheckLogin
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
        if (!Session::has('admin_user')) {
            if(request()->ajax()){
                abort(401);
            }else{
               return redirect()->route('admin.login');
            }
        }
        return $next($request);
    }
}
