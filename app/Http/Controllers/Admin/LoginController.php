<?php

namespace App\Http\Controllers\Admin;

use App\Models\ImUser;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Input;
use Tencent;

class LoginController extends Controller
{
    /**
     * 通过token获取信息
     * @param Request $request
     * @return LoginController
     */
    public function getInfoByToken(Request $request){
        $token = $request->get('token','');
        $user = User::where('token',$token)->first();
        if(!$user){
            return $this->fail(1000201);
        }
        $imUser = ImUser::where('type',3)->where('user_id',$user->id)->first();
        $im_user_id = config('app.env').'_'.$imUser->id;
        $api = new Tencent\TLSSigAPIv2(config('videos.SDK_APP_ID'),config('videos.USER_SIG'));
        $sig = $api->genSig($im_user_id);
        return $this->success([
            'token' => $token,
            'name'  => $user->name,
            'phone'  => $user->phone,
            'im_user_id'=>  $im_user_id,
            'im_user_sig' => $sig,
        ]);
    }

    //用户登录
    public function login(Request $request){
        $account    = Input::get('account','');//手机号或邮箱
        $password   = Input::get('password','');//密码
        if(!$account || !$password){
            return $this->fail(100001);
        }
        $user = User::where( function ($query) use ($account){
            $query->where('email',$account)->orWhere('phone',$account);
        })->first();
        if(!$user){
            return $this->fail(2000007);
        }
        if($user->status == 1){
            return $this->fail(100011);
        }
        if($user->password == md5(md5($password))){
            $token = crateToken($user->id);
            $user->last_login_ip        = $request->getClientIp();
            $user->last_login_time      = date("Y-m-d H:i:s");
            $user->token                = $token;
            $user->token_expire_time    = date("Y-m-d H:i:s",strtotime("+30 day"));
            if($user->save()) {
                $imUser = ImUser::where('type',3)->where('user_id',$user->id)->first();
                $im_user_id = config('app.env').'_'.$imUser->id;
                $api = new Tencent\TLSSigAPIv2(config('videos.SDK_APP_ID'),config('videos.USER_SIG'));
                $sig = $api->genSig($im_user_id);
                return $this->success([
                    'token' => $token,
                    'name' => $user->name,
                    'im_user_id'    =>  $im_user_id,
                    'im_user_sig'   => $sig,
                    'phone'  => $user->phone,
                ]);
            }else{
                return $this->fail();
            }
        }else{
            return $this->fail(100008);
        }
    }
    /**
     * 用户登出
     * @return LoginController
     */
    public function logout(){
        $token  = Input::get('token','');
        $user = User::where('token',$token)->first();
        if(!$user){
            return $this->fail(1000202);
        }else{
            $user->token              = null;
            $user->token_expire_time  = null;
            if($user->save()){
                return $this->success();
            }else{
                return $this->fail(100000);
            }
        }
    }
}
