<?php

namespace App\Http\Controllers\AdminTmp;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\RoleRequest;
use App\Models\Publicity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;

class PublicityController extends Controller
{
    public function index(Request $request){
        $name   = Input::get('name','');
        $phone = Input::get('phone','');
        $where = [];
        if($name){
            $where[] = ['name','like','%'.$name.'%'];
        }
        if($phone){
            $where[] = ['phone','like','%'.$phone.'%'];
        }
        $list = Publicity::where($where)->orderBy('id','desc')->paginate(config('admin.PAGE_SIZE'));
        $list->withPath('?name='.$name.'&phone='.$phone);
        return view('admin.publicity.index',compact('list','name','phone'));
    }

    public function add(){
        return view('admin.publicity.add');
    }

    public function addSave(Request $request){
        $flg = Publicity::create($request->all());
        if($flg){
            return redirect()->route('admin.publicity.index')->with('success','添加成功');
        }else{
            return back()->with('error','添加失败');
        }
    }

    public function edit(){
        $id = Input::get('id');
        $model = Publicity::find($id);
        return view('admin.publicity.edit',compact('model'));
    }

    public function editSave(Request $request){
        $id = Input::get("id",0);
        if(!$id){
            return back()->with('error','参数失败');
        }
        unset($request['_token']);
        $flg = Publicity::where('id',$id)->update($request->all());
        if($flg){
            return redirect()->route('admin.publicity.index')->with('success','编辑成功');
        }else{
            return back()->with('error','编辑失败');
        }
    }


    public function del(){
        $id = Input::get('id',0);
        $flg = Publicity::where('id',$id)->delete();
        if($flg){
            return redirect()->route('admin.publicity.index')->with('success','删除成功');
        }else{
            return back()->with('error','删除失败');
        }
    }
}
