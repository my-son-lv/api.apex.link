<?php

namespace App\Http\Controllers\Admin;

use App\Models\Advert;
use App\Models\Files;
use Faker\Provider\File;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class AdvertController extends Controller
{
    //
    public function list(Request $request){
        $list = Advert::orderBy('id','desc')->paginate($request->pageSize ?? $request->pageSize);
        foreach ($list as $k => $v){
            $v->type = str_replace(1,'小程序广告屏',$v->type);
            $v->type = str_replace(2,'小程序banner位置',$v->type);
            $v->type = str_replace(3,'PC广告弹屏',$v->type);
            $v->type = str_replace(4,'PCbanner位置',$v->type);
        }
        return $this->success($list);
    }

    public function add(Request $request){
        return $this->success(Advert::create(array_filter($request->only('title',
            'start_time',
            'end_time',
            'type',
            'status',
            'img1',
            'url1',
            'img2',
            'url2',
            'img3',
            'url3',
            'img4',
            'url4'
        ))));
    }

    public function edit(Request $request){
        $id = $request->id;
        $data = $request->all();
        unset($data['id']);
        unset($data['token']);
        unset($data['user']);
        $flg = Advert::where('id',$id)->update($data);
        if($flg!==false){
            return $this->success();
        }else{
            return $this->fail();
        }
    }

    public function show(Request $request){
        $desc = Advert::find($request->id);
        $desc->img1 ? $desc->img1_path = Files::find($desc->img1) : null;
        $desc->img2 ? $desc->img2_path = Files::find($desc->img2) : null;
        $desc->img3 ? $desc->img3_path = Files::find($desc->img3) : null;
        $desc->img4 ? $desc->img4_path = Files::find($desc->img4) : null;
        return $this->success($desc);
    }

    public function updateStatus(Request $request){
        $flg = Advert::where('id',$request->id)->update(['status' => $request->status]);
        if($flg !== false ){
            return $this->success( );
        }else{
            return $this->fail();
        }
    }

    public function delete(Request $request){
        if(Advert::where('id',$request->id)->delete()){
            return $this->success();
        }else{
            return $this->fail();
        }
    }
}
