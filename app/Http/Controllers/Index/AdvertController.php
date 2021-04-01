<?php

namespace App\Http\Controllers\Index;

use App\Models\Advert;
use App\Models\Files;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class AdvertController extends Controller
{
    //
    public function advert(Request $request){
        $list = Advert::where('status',1)
            ->whereRaw('FIND_IN_SET('.$request->type.',type)')
            ->where('start_time','<',date("Y-m-d H:i:s"))
            ->where('end_time','>',date("Y-m-d H:i:s"))
            ->get();
        foreach ($list as $k => $v){
            $name = 'img'.$request->type;
            $path = 'img'.$request->type.'_path';
            $v->$name ? $v->$path = Files::find($v->$name) : $v->$path = null;
        }
        return $this->success($list);
    }
}
