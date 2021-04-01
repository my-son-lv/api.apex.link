<?php

namespace App\Http\Controllers\Admin;

use App\Models\Notice;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class NoticeController extends Controller
{
    public function read(Request $request, Notice $notice){

        $count = $notice->where('to_uid',$request->user->id)->where('read_flg',1)->count();
        if($count) {
            $notice->where('to_uid',$request->user->id)->where('read_flg',1)->update(['read_flg' => 2]);
        }
        return $this->success();
    }

    //
    public function notice(Request $request, Notice $notice){
        $page   = $request->get('page',1);
        $pageSize = $request->get('pageSize',config('admin.pageSize'));
        if($page<1) $page = 1;
        $request->type && $notice = $notice->where('type',$request->type);
        $list = $notice->where('to_uid',$request->user->id)->where('read_flg',1);
        $count = ceil($list->count()/$pageSize);
        $list = $list->orderBy('id','desc')->offset(($page-1)*$pageSize)->limit($pageSize)->get();
        return $this->success(['count' => $count,'list' => $list,'page' => $page]);
    }

    public function history(Request $request, Notice $notice){
        $page   = $request->get('page',1);
        $pageSize = $request->get('pageSize',15);
        if($pageSize > 100) $pageSize = 50;
        if($page<1) $page = 1;
        $request->type && $notice = $notice->where('type',$request->type);
        $list = $notice->where('to_uid',$request->user->id);
        $count = ceil($list->count()/$pageSize);
        $list = $list->orderBy('id','desc')->offset(($page-1)*$pageSize)->limit($pageSize)->get();
        return $this->success(['count' => $count,'list' => $list,'page' => $page]);
    }
}
