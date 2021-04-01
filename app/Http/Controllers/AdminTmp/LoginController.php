<?php

namespace App\Http\Controllers\AdminTmp;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Session;

class LoginController extends Controller
{
    //
    public function login(){
        return view('admin.login');
    }

    public function logout(){
        Session::forget('admin_user');
        return redirect()->route('admin.login');
    }

    public function loginCheck(Request $request){
        $account  = Input::get('account','');
        $password = Input::get('password','');
        if(!$account){
            return $this->fail(1000002);
        }
        if(!$password){
            return $this->fail(1000003);
        }
//        $model = User::where(['status' => 0 , 'account' => $account])->first();
        $user = User::where('status',0)->where( function ($query) use ($account){
            $query->where('email',$account)->orWhere('phone',$account);
        })->first();
        if(!$user){
            return $this->fail(1000004);
        }
        if (md5(md5($password)) == $user->password){
            $request->session()->put(['admin_user'=> $user]);
            $user->last_login_time = date("Y-m-d H:i:s",time());
            $user->last_login_ip   = $request->ip();
            $user->save();
            return $this->success();
        }else{
            return $this->fail(1000005);
        }
    }
}
