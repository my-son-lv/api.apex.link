<?php

namespace App\Http\Controllers\Index;

use App\Models\Collect;
use App\Models\Companys;
use App\Models\Country;
use App\Models\Files;
use App\Models\Member;
use App\Models\MemberInfo;
use App\Models\MemberInfoChecked;
use App\Models\Region;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class CollectController extends Controller
{
    //

    public function list(Request $request){
        $language_flg   = $request->get('language_flg',0);//0全部  1母语 2非母语
        $pay_type       = $request->get('pay_type',0);
        $type           = $request->get('type',1); //1收藏 2候选人
        $page           = $request->get('page',1);
        $pageSize       = $request->get('pageSize',config('admin.pageSize'));
        $nationality    = $request->get('nationality','');//国籍
        $token          = $request->get('token','');
        $company = Companys::where('token',$token)->first();

        $list =  MemberInfo::from('collect as a')
            ->leftjoin('members_info as b','a.mid','=','b.mid')
            ->leftjoin('members as c','a.mid','=','c.id')
            ->leftjoin('companys as d','a.cid','=','d.id')
            ->where('a.type',$type)
            ->where('d.id',$company->id);
        if($language_flg){
            $guoji = [];
            if($language_flg == 1){
                $guoji = Country::where('flg',1)->get(['id']);
            }else{
                $guoji = Country::where('flg',0)->get(['id']);
            }
            $list = $list->whereIn('b.nationality',$guoji);
        }
        if($nationality){
            $list = $list->where('b.nationality',$nationality);
        }
        if($pay_type){
            $list = $list->where('b.pay_type',$pay_type);
        }
        $count = ceil($list->count()/$pageSize);
        $list = $list->orderBy('a.id','desc')->offset(($page-1)*$pageSize)->limit($pageSize)->get(['a.*','b.*']);
        foreach ($list as $k => $v){
            if ($v->photos) {
                $v->photos_path = Files::whereIn('id', explode(',', $v->photos))->get();
            }
            $country =  Country::find($v['nationality']);
            $list[$k]['nationality_val'] = $country['code'];
            $list[$k]['country_val'] =  null;
            if($list[$k]['country']){
                $country =  Country::find($v['country']);
                $list[$k]['country_val'] = $country['code'];
            }
            $list[$k]['working_city_datas'] = null;
            if($v->working_city){
                $city_arr = explode(',',$v->working_city);
                $citys = [];
                foreach ($city_arr as $k1 =>$v1){
                    $tmp_city = Region::find($v1);
                    $tmp_pro  = Region::find($tmp_city->pid);
                    $citys[] = [
                        'province_data'=> $tmp_pro,
                        'city_data'    => $tmp_city,
                    ];
                }
                $list[$k]['working_city_datas'] = $citys;
            }
            $list[$k]['china_address_city_data'] = null;
            if($v->china_address && $v->in_domestic == 1){
                $tmp_city = Region::find($v->china_address);
                $tmp_pro  = Region::find($tmp_city->pid);
                $list[$k]['china_address_city_data'] = [
                    'province_data'=> $tmp_pro,
                    'city_data'    => $tmp_city,
                ];
            }
            unset($v['wechat']);
        }
        return $this->success(['count' => $count,'list' => $list,'page' => $request->page ?? 1]);
    }

    /**
     * 收藏 type 1 候选人 2
     * class 1收藏 添加候选人 2取消收藏 取消候选人
     * mid  添加时需要
     * id   删除时需要
     * @param Request $request
     * @return CollectController
     */
    public function collect(Request $request){
        $type   =  $request->get('type',1); //1收藏 2候选人
        $token  =  $request->get('token','');
        $class  =  $request->get('class',1);//1收藏 添加候选人 2取消收藏 取消候选人
        $mid    =  $request->get('mid',0);//外教id
//        $id     =  $request->get('id',0);//记录id


        $company = Companys::where('token',$token)->first();
        if($class == 1 ){
            $flg = Collect::where('mid',$mid)->where('cid',$company->id)->where('type',$type)->count();
            if($flg){
                return $this->fail(2000008);
            }

            $model = new Collect();
            $model->type = $type;
            $model->cid  = $company->id;
            $model->mid  = $mid;
            if($model->save()){
                return $this->success();
            }else{
                return $this->fail();
            }
        }else{
            $flg1 = Collect::where('mid',$mid)->where('cid',$company->id)->where('type',$type)->count();
            if(!$flg1){
                return $this->fail(2000201);
            }
            $flg = Collect::where('mid',$mid)->where('cid',$company->id)->delete();
            if($flg){
                return $this->success();
            }else{
                return $this->fail();
            }
        }


    }
}
