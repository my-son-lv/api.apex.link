<?php

namespace App\Http\Middleware;

use App\Models\Member;
use App\Models\User;
use Closure;
use Illuminate\Support\Facades\Input;

class checkAdminToken
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
        $token = Input::get('token','');
        if(!$token){
            return $this->fail(1000200);
        }
        $user = User::where("token",$token)->first();
        if(!$user){
            return $this->fail(1000201);
        }
        if(strtotime($user->token_expire_time)<time()){
            return $this->fail(1000202);
        }
        if((strtotime($user->token_expire_time)-time())>60*60*24){
        $user->token_expire_time = date("Y-m-d H:i:s",strtotime("+30 day"));
            $user->save();
        }
        $request->merge(['user' => $user]);
        return $next($request);
    }

    public function fail($code )
    {
        return response()->json([
            'code'    => $code,
            'msg' => config('errorcode.code')[(int) $code],
        ])->setEncodingOptions(JSON_UNESCAPED_UNICODE);
    }
}
