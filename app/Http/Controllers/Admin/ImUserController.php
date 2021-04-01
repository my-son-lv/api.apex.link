<?php

namespace App\Http\Controllers\Admin;

use App\Models\CompanyAdvier;
use App\Models\Companys;
use App\Models\Files;
use App\Models\ImUser;
use App\Models\Member;
use App\Models\MemberInfo;
use App\Models\MemberInfoChecked;
use App\Models\User;
use Faker\Provider\File;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ImUserController extends Controller
{
    //
    public function getImUserList(Request $request){
        $token = $request->get('token','');
        $user = User::where('token',$token)->first();

        //获取所有外教列表
        /*$teachList = Member::get(['id','nick_name']);
        foreach ($teachList as $k => $v){
            //如果审核通过 用first_name + last_name  否则 nick_name
            //获取头像 审核通过 用户照片 否则  默认图片
            $member = MemberInfoChecked::where('mid',$v->id)->first();
            if($member){
                $v['sex'] = $member->sex;
                $v->nick_name = ($member->name && $member->last_name) ? $member->name.' '.$member->last_name : $v->nick_name;
                if($member->photos){
                    $v->photos = Files::whereIn('id',explode(',',$member->photos))->get();
                }else{
                    $v->photos = null;
                }
            }else{
                $v['sex'] = 0;
                $v->photos = null;
            }
            //获取im_user_id
            $im = ImUser::where('type',1)->where('user_id',$v->id)->first();
            $v['im_user_id'] = config('app.env').'_'.$im->id;
        }*/


        //我的获取企业
        $cidList = CompanyAdvier::where('uid',$user->id)->get(['cid']);
        $companyList= Companys::whereIn('id',$cidList)->where('status',2)->get(['id','logo as photos','company_name as nick_name']);
        foreach ($companyList as $k => $v){
            $im = ImUser::where('type',2)->where('user_id',$v->id)->first();
            $v['im_user_id'] = config('app.env').'_'.$im->id;
            if($v->photos){
                $v->photos = Files::whereIn('id',explode(',',$v->photos))->get();
            }else{
                $v->photos = null;
            }

        }
        //其他企业
        $list = Companys::where('status',2)->get(['id','logo as photos','company_name as nick_name']);
        foreach ($list as $k => $v){
            $im = ImUser::where('type',2)->where('user_id',$v->id)->first();
            $v['im_user_id'] = config('app.env').'_'.$im->id;
            if($v->photos){
                $v->photos = Files::whereIn('id',explode(',',$v->photos))->get();
            }else{
                $v->photos = null;
            }
        }
        return $this->success(['teach' => $list,'company' => $companyList]);
    }
}
