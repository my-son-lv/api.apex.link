<?php

namespace App\Http\Controllers\Index;

use App\Models\JobCollect;
use App\Models\Region;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class JobCollectController extends Controller
{
    //
    public function jobCollect(Request $request){
        $type   = $request->get('type',1);//1收藏 2取消收藏
        $jid    = $request->get('jid',0);//职位id
        $flg  = JobCollect::where(['mid' => $request->member->id , 'jid' => $jid])->count();
        if($type == 1){
            if($flg){
                return $this->fail(2000220);
            }
            JobCollect::create(['mid' => $request->member->id , 'jid' => $jid]);
        }else{
            if(!$flg){
                return $this->fail(2000201);
            }
            JobCollect::where(['mid' => $request->member->id , 'jid' => $jid])->delete();
        }
        return $this->success();
    }


    public function jobCollectList(Request $request){
        $list = JobCollect::from('job_collects as a')
            ->join('job as b','a.jid','=','b.id')
            ->where('a.mid',$request->member->id);
        $request->name && $list = $list->where('b.name','like',"%{$request->name}%");
        $count = ceil($list->count()/($request->pageSize ?? 15));
        $list = $list->orderBy('id','desc')
            ->select(['a.jid','b.*'])
            ->paginate($request->pageSize ?? $request->pageSize)
            ->each(function ($v){
                if($v->job_city){
                    $v->job_area_data = Region::find($v->job_city);
                    $v->job_city_data = Region::find($v->job_area_data->pid);
                    $v->job_province_data  = Region::find($v->job_city_data->pid);
                }else{
                    $v->job_area_data =  [];
                    $v->job_city_data  = [];
                    $v->job_province_data  = [];
                }
            });
        return $this->success(['count' => $count,'list'=> $list, 'page' => $request->page ?? 1]);
    }

}
