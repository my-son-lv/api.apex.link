<?php

namespace App\Http\Controllers\AdminTmp;

use App\Models\Region;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class PublicController extends Controller
{
    //

    /*
     * 角色管理 获取菜单列表
     */
    public function getCityZtree(){
        $city = Region::where('id','>',100000)->where('level','<',3)->orderBy('id','asc')->get(['id','pid as pId','pinyin as name'])->toArray();
        foreach ($city as $k1 => $v1){
            $city[$k1]['open'] = true;
        }
        return response()->json($city);
    }
}
