<?php

namespace App\Http\Controllers\Index;

use App\Models\Region;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Input;

class RegionController extends Controller
{
    public function getCityByChar(Request $request){

        $list = new Region();
        if($request->char){
            $list = $list->whereIn('char',explode(',',$request->char));
        }
        $list = $list->where('level',2)->orderBy('id','asc')->get();
        return $this->success($list);
    }

    //获取省份
    public function getProvince(){
        $list = Region::where('pid',100000)->get();
        return $this->success($list);
    }
    //获取城市
    public function getCity(){
        $code = Input::get('id');
        if(!$code){
            return $this->fail('100001');
        }
        $list = Region::where('pid',$code)->get();
        return $this->success($list);

    }

    //获取城市
    public function getCitys(){
        $code = Input::get('id',100000);
        $list = Region::where('pid',$code)->get();
        return $this->success($list);
    }
}
