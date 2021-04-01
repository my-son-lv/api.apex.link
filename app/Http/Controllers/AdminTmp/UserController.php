<?php

namespace App\Http\Controllers\AdminTmp;

use App\Http\Requests\Admin\UserRequest;
use App\Models\Role;
use App\Models\User;
use App\Http\Controllers\Controller;
use App\Models\UserRole;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Input;

class UserController extends Controller
{
    public function index(){
        $name   = Input::get('name','');
        $status = Input::get('status','');
        $phone  = Input::get('phone','');
        $where = [];
        if($name){
            $where[] = ['name','like','%'.$name.'%'];
        }
        if($status!=''){
            $where[] = ['status','=',$status];
        }
        if($phone){
            $where[] = ['phone','like','%'.$phone.'%'];
        }
        $list = User::where($where)->orderBy('id','desc')->paginate(config('admin.PAGE_SIZE'));
        $list->withPath('?name='.$name.'&phone='.$phone.'&status='.$status);
        return view('admin.user.index',compact('list','name','status','phone'));
    }

    public function add(){
        $role = Role::where('delete',0)->get();
        return view('admin.user.add',compact('role'));
    }

    public function addSave(UserRequest $request){
        $data = $request->all();
        $phone_flg = User::where('phone',$data['phone'])->count();
        if($phone_flg){
            return back()->with('error','手机号已存在');
        }
        $email_flg = User::where('email',$data['email'])->count();
        if($email_flg){
            return back()->with('error','邮箱已存在');
        }
        DB::beginTransaction();
        try{
            $model = new User();
            $model->name    = $data['name'];
            $model->phone   = $data['phone'];
            $model->email   = $data['email'];
            $model->status  = $data['status'];
            $model->password= $data['password'] ? md5(md5($data['password'])) : md5(md5('123456'));
            if($model->save()){
                foreach ($data['roles'] as $k => $v){
                    $roleUser = new UserRole();
                    $roleUser->user_id = $model->id;
                    $roleUser->role_id = $v;
                    if(!$roleUser->save()){
                        DB::rollback();//事务回滚
                        return back()->with('error','添加失败');
                    }
                }
                DB::commit();
                return redirect()->route('admin.user.index')->with('success','添加成功');
            }else{
                DB::rollback();//事务回滚
                return back()->with('error','添加失败');
            }

        } catch (\Exception $e){
            DB::rollback();//事务回滚
            return back()->with('error',$e->getMessage());
        }
    }

    public function edit(){
        $id = Input::get('id');
        $model = User::find($id);
        $role = Role::where('delete',0)->get();
        $roleUser = UserRole::where('user_id',$model->id)->get(['role_id']);
        return view('admin.user.edit',compact('model','role','roleUser'));
    }

    public function editSave(UserRequest $request){
        $id = Input::get("id",0);
        $model = User::find($id);
        $data = $request->all();
        $phone_flg = User::where('phone',$data['phone'])->where('id','<>',$id)->count();
        if($phone_flg){
            return back()->with('error','手机号已存在');
        }
        $email_flg = User::where('email',$data['email'])->where('id','<>',$id)->count();
        if($email_flg){
            return back()->with('error','邮箱已存在');
        }

        DB::beginTransaction();
        try{
            $model->name    = $data['name'];
            $model->phone   = $data['phone'];
            $model->email   = $data['email'];
            $model->status  = $data['status'];
            $model->password= $data['password'] ? md5(md5($data['password'])) : md5(md5('123456'));
            if($model->save()!==false){
                UserRole::where('user_id',$id)->delete();
                foreach ($data['roles'] as $k => $v){
                    $roleUser = new UserRole();
                    $roleUser->user_id = $model->id;
                    $roleUser->role_id = $v;
                    if(!$roleUser->save()){
                        DB::rollback();//事务回滚
                        return back()->with('error','编辑失败');
                    }
                }
                DB::commit();
                return redirect()->route('admin.user.index')->with('success','编辑成功');
            }else{
                DB::rollback();//事务回滚
                return back()->with('error','编辑失败');
            }
        } catch (\Exception $e){
            DB::rollback();//事务回滚
            return back()->with('error',$e->getMessage());
        }
    }

    public function status(){
        $id = Input::get('id',0);
        $model = User::find($id);
        if($model){
            $model->status =  $model->status==0 ? 1 : 0;
            if($model->save()){
                return $this->success();
            }else{
                return $this->fail(1000001);
            }
        }else{
            return $this->fail(1000001);
        }
    }

    public function del(){
        $id = Input::get('id',0);
        $flg = User::where('id',$id)->delete();
        if($flg){
            return $this->success();
        }else{
            return $this->fail(1000001);
        }
    }

    public function upPwd(){
        $oldPwd = Input::get('old_pwd');
        $newPwd = Input::get('new_pwd');
        if(!$oldPwd){
            return $this->fail(1000001);
        }
        if(!$newPwd){
            return $this->fail(1000001);
        }
        $model = User::find(session('admin_user')['id']);
        if(md5(md5($oldPwd)) == $model->password){
            $model->password= md5(md5($newPwd));
            if($model->save() !==false){
                return $this->success();
            }
        }else{
            return $this->fail(1000006);
        }
    }

}
