<?php

namespace App\Http\Controllers\Admin;

use App\Models\Member;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Input;

class UserController extends Controller
{
    #用户列表
    public function userList(){
        $name   = Input::get('name','');
        $email  = Input::get('email','');
        $phone  = Input::get('phone','');
        $page   = Input::get('page',1);
        $pageSize = Input::get('pageSize',config('admin.pageSize'));
        if($page<1) $page = 1;

        $list = User::where('id','>',0);
        if($name){
            $list = $list->where('name','like','%'.$name.'%');
        }
        if($email){
            $list = $list->where('email','like','%'.$email.'%');
        }
        if($phone){
            $list = $list->where('phone','like','%'.$phone.'%');
        }
        $count = ceil($list->count()/$pageSize);
        $list = $list->orderBy('id','desc')->offset(($page-1)*$pageSize)->limit($pageSize)->get();

        return $this->success(['count' => $count,'list' => $list]);
    }

    #用户启用禁用
    public function userStatus(){
        $type = Input::get('type',0); //0启用 1禁用
        $id   = Input::get('id',0);
        $token= Input::get('token',0);

        if(!$id){
            return $this->fail(2000001);
        }
        $member = Member::where('token',$token)->first();
        if($member->id == $id){
            return $this->fail(2000002);
        }

        $flg = User::where('id',$id)->update(['status' => $type ]);
        if($flg){
            return $this->success();
        }else{
            return $this->fail();
        }
    }
    #用户重置密码
    public function userUpdatePassword(){
        $id   = Input::get('id',0);
        if(!$id){
            return $this->fail(2000001);
        }
        $flg = User::where('id',$id)->update(['password' => md5(md5('123456')) ]);
        if($flg){
            return $this->success();
        }else{
            return $this->fail();
        }
    }

    #添加用户
    public function addUser(){
        $name       = Input::get('name','');
        $email      = Input::get('email','');
        $phone      = Input::get('phone','');
        $status     = Input::get('status',0);
        $type       = Input::get('type',0);

        $password   = md5(md5('123456'));
        if(!$name || !$email || !$phone ){
            return $this->fail(100001);
        }
        #校验邮箱号是否存在
        if(User::isEmailExist($email)){
            return $this->fail(2000003);
        }
        #校验手机号是否存在
        if(User::isPhoneExist($phone)){
            return $this->fail(2000004);
        }
        $model = new User();
        $model->name    = $name;
        $model->email   = $email;
        $model->phone   = $phone;
        $model->status  = $status;
        $model->type    = $type;
        $model->password= $password;
        if($model->save()){
            return $this->success();
        }else{
            return $this->fail();
        }
    }

    #用户详情
    public function descUser(){
        $id         = Input::get('id',0);
        if(!$id){
            return $this->fail(100001);
        }
        $model = User::find($id);
        if(!$model){
            return $this->fail(2000005);
        }
        return $this->success($model);
    }

    #用户编辑
    public function editUser(){
        $id         = Input::get('id',0);
        $name       = Input::get('name','');
        $email      = Input::get('email','');
        $phone      = Input::get('phone','');
        $status     = Input::get('status',0);
        $type       = Input::get('type',0);

        $password   = md5(md5('123456'));
        if(!$id ||!$name || !$email || !$phone ){
            return $this->fail(100001);
        }

        #校验邮箱号是否存在
        if(User::isEmailExist($email,$id)){
            return $this->fail(2000003);
        }
        #校验手机号是否存在
        if(User::isPhoneExist($phone,$id)){
            return $this->fail(2000004);
        }
        $model = User::find($id);
        if(!$model){
            return $this->fail(2000005);
        }

        $model->name    = $name;
        $model->email   = $email;
        $model->phone   = $phone;
        $model->status  = $status;
        $model->type    = $type;
        $model->password= $password;
        if($model->save()){
            return $this->success();
        }else{
            return $this->fail();
        }
    }

}
