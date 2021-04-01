<?php

namespace App\Http\Middleware;

use App\Models\Companys;
use App\Models\Member;
use Closure;
use Illuminate\Support\Facades\Input;

class checkCompanyToken
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
        $company = Companys::where("token",$token)->first();
        if(!$company){
            return $this->fail(1000201);
        }
        if(strtotime($company->token_expire_time)<time()){
            return $this->fail(1000201);
        }
        if((strtotime($company->token_expire_time)-time())>60*60*24){
            $company->token_expire_time = date("Y-m-d H:i:s",strtotime("+30 day"));
            $company->save();
        }
        $request->merge(['company' => $company]);
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
