<?php

namespace App\Http\Controllers\AdminTmp;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\RoleRequest;
use App\Models\Role;
use App\Models\RoleMenu;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;

class RoleController extends Controller
{
    public function index(){
        $name   = Input::get('name','');
        $status = Input::get('status','');
        $where[] = ['delete','=',0];
        if($name){
            $where[] = ['name','like','%'.$name.'%'];
        }
        if($status!=''){
            $where[] = ['status','=',$status];
        }
        $list = Role::where($where)->orderBy('id','desc')->paginate(config('admin.PAGE_SIZE'));
        $list->withPath('?name='.$name.'&status='.$status);
        return view('admin.role.index',compact('list','name','status'));
    }

    public function add(){
        return view('admin.role.add');
    }

    public function addSave(RoleRequest $request){
        $data = $request->all();
        DB::beginTransaction();
        try{
            $model = new Role();
            $model->name    = $data['name'];
            $model->status  = $data['status'];
            if($model->save()){
                $roleMenuList = explode(',',$data['menu_list']);
                foreach ($roleMenuList as $k => $v){
                    $RoleMenu = new RoleMenu();
                    $RoleMenu->role_id = $model->id;
                    $RoleMenu->menu_id = $v;
                    if(!$RoleMenu->save()){
                        DB::rollback();//事务回滚
                        return back()->with('error','添加失败');
                    }
                }
                DB::commit();
                return redirect()->route('admin.role.index')->with('success','添加成功');;
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
        $model = Role::find($id);
        return view('admin.role.edit',compact('model'));
    }

    public function editSave(RoleRequest $request){
        $id = Input::get("id",0);
        $model = Role::find($id);
        $data = $request->all();
        DB::beginTransaction();
        try{
            $model->name    = $data['name'];
            $model->status  = $data['status'];
            if($model->save()){
                RoleMenu::where('role_id',$id)->delete();
                $roleMenuList = explode(',',$data['menu_list']);
                foreach ($roleMenuList as $k => $v){
                    $RoleMenu = new RoleMenu();
                    $RoleMenu->role_id = $model->id;
                    $RoleMenu->menu_id = $v;
                    if(!$RoleMenu->save()){
                        DB::rollback();//事务回滚
                        return back()->with('error','添加失败');
                    }
                }
                DB::commit();
                return redirect()->route('admin.role.index')->with('success','编辑成功');
            }else{
                DB::rollback();//事务回滚
                return back()->with('error','添加失败');
            }
        } catch (\Exception $e){
            DB::rollback();//事务回滚
            return back()->with('error',$e->getMessage());
        }
    }

    public function status(){
        $id = Input::get('id',0);
        $model = Role::find($id);
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
        $model = Role::find($id);
        if($model){
            $model->delete =  1;
            if($model->save()){
                return $this->success();
            }else{
                return $this->fail(1000001);
            }
        }else{
            return $this->fail(1000001);
        }
    }
}
