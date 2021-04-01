<?php

namespace App\Http\Controllers\Admin;

use App\Models\Companys;
use App\Models\Country;
use App\Models\Event;
use App\Models\Files;
use App\Models\Job;
use App\Models\MemberInfo;
use App\Models\Region;
use App\Models\Vip;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Input;

class PublicController extends Controller
{
    public function jobOnList(Request $request){
        return Companys::from('companys as a')
            ->rightjoin('job as b','a.id','=','b.cid')
            ->where('a.status',2)
            ->where('b.status',1)
            ->get(['b.id','a.company_name','b.name','b.cid']);
    }

    public function memberAllList(Request $request,MemberInfo $memberInfo){
        $memberInfo = $memberInfo->orderBy('id','desc')->get(['name','last_name','mid','id','photos']);
        $memberInfo->each(function ($memberInfo){
            $memberInfo->photos_path = Files::find($memberInfo->photos) ?? null;
        });
        return $this->success($memberInfo);
    }

    public function getVipList(Request $request){
        return $this->success(Vip::where('status',1)->get());
    }

    public function getTerraceLog(Request $request){
        $type = $request->get('type',1);
        $id = $request->get('id',0);

        if(!$id){
            return $this->fail(100001);
        }
        $list = Event::where('user_id',$id)->where('type',$type)->get();
        return $this->success($list);
    }

    //公司列表
    public function getCompaanyList(){
        $list = Companys::where('status',2)->orderBy('id','desc')->get(['id','company_name']);
        return $this->success($list);
    }

    //获取省市区
    public function getCitys(){
        $code = Input::get('id',100000);
        $list = Region::where('pid',$code)->get();
        return $this->success($list);
    }

    /**
     * 获取国籍列表
     * @param Request $request
     * @return PublicController
     */
    public function getNationList(Request $request){
        $type = $request->get('type',0);//0全部 1母语国籍  2非母语国家
        $list = Country::where('id','>',0);
        if($type == 1){
            $list = $list->where('flg',1);
        }elseif($type == 2){
            $list = $list->where('flg',0);
        }
        $list = $list->get();
        return $this->success($list);
    }
}
