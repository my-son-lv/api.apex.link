<?php

namespace App\Http\Controllers\AdminTmp;

use App\Models\Country;
use App\Models\Files;
use App\Models\ImUser;
use App\Models\Member;
use App\Models\MemberInfo;
use App\Models\MemberInfoChecked;
use App\Models\Region;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Log;

class MemberController extends Controller
{
    //

    public function add(){
        $countrys = Country::all();
        $province = Region::where('pid',100000)->get();
        return view('admin.member.add1',compact('countrys','province'));
    }

    public function addSave(Request $request){
        $email      = $request->get('email','');
        if(!$email) return back()->with('error','邮箱不能为空');
        $flg = Member::where('email',$email)->count();
        if($flg)  return back()->with('error','用户已存在，请勿重复提交');
        $nick_name  = $request->get('nick_name','');
        if(!$nick_name) return back()->with('error','昵称不能为空');
        $first_name     = $request->get('name','');
        if(!$first_name) return back()->with('error','First Name不能为空');
        $last_name      = $request->get('last_name','');
        $sex            = $request->get('sex',2);
        $brithday       = $request->get('brithday','');
        $phone          = $request->get('phone','');
        $school         = $request->get('school','');
        $nationality    = $request->get('nationality',1);
        $pay_type       = $request->get('pay_type',1);
        $university     = $request->get('university',1);
        $working_seniority  = $request->get('working_seniority',null);
        if(!$working_seniority) return back()->with('error','期望工作地不能为空');
        $major          = $request->get('major','');
        $desc           = $request->get('desc','');
        $photos         = $request->get('photos','');
        $videos         = $request->get('videos','');
        $wechat         = $request->get('wechat','');
        $china_address  = $request->get('china_address',0);
        $working_city   = $request->get('working_city',0);
        $edu_cert_imgs  = $request->get('edu_cert_imgs','');
        $edu_auth_imgs  = $request->get('edu_auth_imgs','');
        $notes          = $request->get('notes',null);
        $in_domestic    = $request->get('in_domestic',0);//是否在国内 0不在 1在
        $visa_type      = $request->get('visa_type','');//签证类型  1 Z  2 M 3 F 4 X 5 others
        $country        = $request->get('country','');
        $visa_exp_date  = $request->get('visa_exp_date','');
        $work_flg       = $request->get('work_flg',0);
        $edu_cert_flg   = $request->get('edu_cert_flg',0);
        $edu_auth_flg   = $request->get('edu_cert_flg',0);


        DB::beginTransaction();
        try{
            //插入外教用户表
            $member = new Member();
            $member->email      = $email;
            $member->password   =  md5(md5('1234567890'));
            $member->nick_name  =  $nick_name;
            $member->user_id    = $this->genRequestSn(rand(100000,999999));
            $member->sign_id    = $this->genRequestSn(rand(1000000,9999999));
            if($member->save()){
                //插入外教审核表
                //必填
                $memberCheck = new MemberInfoChecked();
                $memberCheck->mid       = $member->id;
                $memberCheck->name      = $first_name;
                $last_name  && $memberCheck->last_name = $last_name;
                $memberCheck->sex       = $sex;
                $brithday   && $memberCheck->brithday  = $brithday;
                $phone      && $memberCheck->phone     = $phone;
                $school     && $memberCheck->school    = $school;
                $nationality && $memberCheck->nationality   = $nationality;
                $memberCheck->pay_type      = $pay_type;
                $memberCheck->university    = $university;
                $memberCheck->working_seniority = $working_seniority;
                $major      && $memberCheck->major             = $major;
                $desc       && $memberCheck->desc              = $desc;
                $photos     && $memberCheck->photos            = $photos;
                $videos     && $memberCheck->videos            = $videos;
                $notes      && $memberCheck->notes             = $notes;
                $memberCheck->in_domestic      = $in_domestic;
                if($in_domestic == 1){
                    $visa_type && $memberCheck->visa_type       = $visa_type;
                    $china_address && $memberCheck->china_address   = $china_address;
                    $memberCheck->country         = null;
                    $visa_exp_date && $memberCheck->visa_exp_date   = $visa_exp_date;
                }else{
                    $memberCheck->visa_type       = null;
                    $memberCheck->china_address   = null;
                    $country && $memberCheck->country         = $country;
                    $memberCheck->visa_exp_date   = null;
                }
                $wechat         &&      $memberCheck->wechat        = $wechat;
                $china_address  &&      $memberCheck->china_address = $china_address;
                $working_city   &&      $memberCheck->working_city  = $working_city;
                $memberCheck->edu_cert_flg  = $edu_cert_flg;
                if($edu_cert_imgs){
                    $memberCheck->edu_cert_imgs = $edu_cert_imgs;
                }
                $memberCheck->edu_auth_flg  = $edu_auth_flg;
                if($edu_auth_imgs){

                    $memberCheck->edu_auth_imgs = $edu_auth_imgs;
                }
                $memberCheck->work_flg          = $work_flg;
                //状态设置
                $memberCheck->status = 2;
                if($memberCheck->save()){
                    $memberInfo = new MemberInfo();
                    unset($memberCheck['id']);
                    unset($memberCheck['status']);
                    unset($memberCheck['check_log_id']);
                    unset($memberCheck['created_at']);
                    unset($memberCheck['updated_at']);
                    $memberCheck1 = $memberCheck->toArray();
                    foreach ($memberCheck1 as $k => $v){
                        $memberInfo->$k = $v;
                    }
                    if($memberInfo->save()){
                        $model1 = new ImUser();
                        $model1->type = 1;
                        $model1->user_id = $member->id;
                        if($model1->save()){
                            if(config('app.env')!='local'){
                                $res = $this->createImOneAccount(['Identifier'=>config('app.env').'_'.$model1->id,'Nick'=>$memberCheck->first_name.' '.$memberCheck->last_name,'FaceUrl'=> $memberCheck->photos ? Files::where('id',$memberCheck->photos)->pluck('path')->first() : ($memberCheck->sex == 0 ? $this->getDefaultLogo(4)[0]['path'] : $this->getDefaultLogo(5)[0]['path'])]);
                                $res = json_decode($res,true);
                                if($res['ActionStatus'] == 'OK'){
                                    //发送消息
                                    $imUser = ImUser::where('user_id',15)->where('type',3)->first();
                                    $sendMsgRes = $this->sendImMsg(
                                        config('app.env').'_'.$imUser->id,
                                        config('app.env').'_'.$model1->id,
                                        'Your exclusive customer service has been online, you can consult at any time if you have any questions'
                                    );
                                    $sendMsgRes = json_decode($sendMsgRes,true);
                                    if($sendMsgRes['ActionStatus']!='OK'){
                                        DB::rollback();
                                        return back()->with('error','IM消息发送失败,错误码:'.$res['ErrorCode'].' 错误描述:'.$res['ErrorInfo']);
                                    }else{
                                        DB::commit();
                                        return redirect('admin/member/add')->with('success','添加成功');
                                    }
                                }else{
                                    DB::rollback();
                                    return back()->with('error','IM注册失败了,错误码:'.$res['ErrorCode'].' 错误描述:'.$res['ErrorInfo']);
                                }
                            }else{
                                DB::commit();
                                return redirect('admin/member/add')->with('success','添加成功');
                            }
                        }else{
                            DB::rollback();
                            return back()->with('error','插入IM表失败');
                        }
                    }else{
                        DB::rollback();
                        return back()->with('error','插入外教正式表失败');
                    }
                }else{
                    DB::rollBack();
                    return back()->with('error','插入外教审核表失败');
                }
            }else{
                DB::rollBack();
                return back()->with('error','插入外教用户表失败');
            }
        }catch (\Exception $e){
            DB::rollBack();
            return back()->with('error',$e->getMessage());
        }
    }
}
