<?php

namespace App\Http\Controllers\AdminTmp;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\MenuRequest;
use App\Models\Menu;
use App\Models\RoleMenu;
use Illuminate\Support\Facades\Input;

class MenuController extends Controller
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
        $list = Menu::where($where)->orderBy('id','desc')->paginate(config('admin.PAGE_SIZE'));
        $list->withPath('?name='.$name.'&status='.$status);
        return view('admin.menu.index',compact('list','name','status'));
    }

    public function add(){
        return view('admin.menu.add');
    }

    public function addSave(MenuRequest $request){
        $data = $request->all();
        $model = new Menu();
        $model->pid     = $data['pid'];
        $model->status  = $data['status'];
        $model->name    = $data['name'];
        $model->route   = $data['route'];
        $model->sort    = $data['sort'] ? $data['sort'] : 0;
        if($model->save()){
            return redirect()->route('admin.menu.index')->with('success','添加成功');
        }else{
            return back()->with('error','添加失败');
        }
    }

    public function edit(){
        $id = Input::get('id');
        $model = Menu::find($id);
        $model->pname =  $model->pid == 0 ? '顶级菜单' : Menu::find($model->pid)['name'];

        return view('admin.menu.edit',compact('model'));
    }

    public function editSave(MenuRequest $request){
        $id     = Input::get("id",0);
        $model  = Menu::find($id);
        $data = $request->all();
        $model->pid     = $data['pid'];
        $model->status  = $data['status'];
        $model->name    = $data['name'];
        $model->route   = $data['route'];
        $model->sort    = $data['sort'] ? $data['sort'] : 0;
        if($model->save()!==false){
            return redirect()->route('admin.menu.index')->with('success','编辑成功');
        }else{
            return back()->with('error','编辑失败');
        }

    }

    public function status(){
        $id = Input::get('id',0);
        $model = Menu::find($id);
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
        $model = Menu::find($id);
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

    /*
     * 角色管理 获取菜单列表
     */
    public function menu_list(){
        $id = (int)Input::get('id',0);
        $type = Input::get('type',0);       //type ==1  菜单管理下拉框  0角色管理下拉选择
        $menu = Menu::where('status','0')->where('delete',0)->orderBy('sort','asc')->get(['id','pid as pId','name'])->toArray();
        if($id>0){
            $xz = RoleMenu::where(['role_id' => $id ])->get();
            foreach ($menu as $k => $v) {
                foreach ($xz as $key => $value) {
                    if($value->menu_id == $v['id']){
                        $menu[$k]['checked'] = true;
                    }
                }
            }
        }
        foreach ($menu as $k1 => $v1){
            $menu[$k1]['open'] = false;
        }
        if($type==1){
            if($id>0 && in_array(0,array_column($xz->toArray(),'menu_id'))){
                array_push($menu,array('id'=>0,'pId'=>0,'name'=>'顶级菜单','open'=>true,'checked' => true));
            }else{
                array_push($menu,array('id'=>0,'pId'=>0,'name'=>'顶级菜单','open'=>true));
            }
        }
        return response()->json($menu);
    }

}
