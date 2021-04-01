<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;

class AccessControlAllowOrigin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        header('Access-Control-Allow-Origin: *');
        header("Access-Control-Allow-Credentials: true");
        header("Access-Control-Allow-Methods: *");
        header('Access-Control-Allow-Headers: Origin, Access-Control-Request-Headers, SERVER_NAME, Access-Control-Allow-Headers, cache-control, token, X-Requested-With, Content-Type, Accept, Connection, User-Agent, Cookie, X-XSRF-TOKEN');
        if($request->getMethod() !== 'OPTIONS') {
            header("Access-Control-Expose-Headers: *");
        }
        /*Log::info(URL::current());
        $data  = $request->all();
        Log::info(json_encode($data));
        return $next($request);*/
        //请求参数
        Log::info('--------------------');
        Log::info('请求URL:'.URL::current());
        $data  = $request->all();
        Log::info('请求参数:'.json_encode($data));
        $t1=microtime(true);
//        DB::connection()->enableQueryLog();
        try{
            $response = $next($request);
        }catch (Exception $e) {
            $response = $this->handleException($request, $e);
        }catch (Error $error) {
            $e = new FatalThrowableError($error);
            $response = $this->handleException($request, $e);
        }
        $t2=microtime(true);
//        Log::info('运行SQL:'.json_encode(DB::getQueryLog()));
        Log::info('运行时间:'.round($time1=$t2-$t1,5));
        Log::info('请求结果:'.json_encode(@$response->original));
        return $response;
    }

}
