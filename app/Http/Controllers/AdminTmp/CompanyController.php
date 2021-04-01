<?php

namespace App\Http\Controllers\AdminTmp;

use App\Models\CompanyAdvier;
use App\Models\Companys;
use App\Models\Country;
use App\Models\Files;
use App\Models\ImUser;
use App\Models\Region;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CompanyController extends Controller
{
    //
    /*public function index(Request $request){
        $list = Companys::orderBy('id','desc')->paginate(config('admin.PAGE_SIZE'));
//        $list->withPath('?name='.$name.'&phone='.$phone.'&status='.$status);
        return View('admin.company.index',compact('list'));
    }*/


    public function add(){
        $countrys = Country::all();
        $province = Region::where('pid',100000)->get();
        $user = User::where('status',0)->get();
        return view('admin.company.add',compact('countrys','province','user'));
    }

    public function addSave(Request $request){
        $company_name   =   $request->get('company_name','');
        $phone          =   $request->get('phone','');
        $password       =   md5(md5('1234567890'));
        $type           =   $request->get('type','');
        $city           =   $request->get('city',0);
        $address        =   $request->get('address','');
        $business_flg   =   $request->get('business_flg',0);
        $talent         =   $request->get('talent',0);
        $student_age    =   $request->get('student_age','');
        $abroad_staff   =   $request->get('abroad_staff',0);
        $needs_num      =   $request->get('needs_num',0);
        $pay            =   $request->get('pay',1);
        $contact        =   $request->get('contact','');
        $contact_phone  =   $request->get('contact_phone','');
        $work_email     =   $request->get('work_email','');
        $user           =   $request->get('user','');

        $business_name  =   $request->get('business_name','');
        $logo           =   $request->get('logo','');
        $business_img   =   $request->get('business_img','');
        $talent_img     =   $request->get('talent_img','');
        $school_img_1   =   $request->get('school_img_1','');
        $school_img_2   =   $request->get('school_img_2','');
        if(!$company_name) return back()->with('error','机构名称不能为空');
        if(!$phone) return back()->with('error','登录手机号不能为空');
        $flg = Companys::where('phone',$phone)->count();
        if($flg)  return back()->with('error','用户已存在，请勿重复提交');
        if(!$type) return back()->with('error','请选择机构类型');
        if(!$city) return back()->with('error','请选择办公地址');
        if(!$address) return back()->with('error','请填写详细地址');
        if(!$student_age) return back()->with('error','请选择学生年龄');
        if(!$student_age) return back()->with('error','请选择学生年龄');
        if(!$needs_num) return back()->with('error','请填写年需求外籍员工数');
        if(!$contact) return back()->with('error','请填写紧急联系人');
        if(!$contact_phone) return back()->with('error','请填写紧急联系人电话');
//        if(!$work_email) return back()->with('error','请填写紧急联系人工作邮箱');
        DB::beginTransaction();
        try{
            $model = new Companys();
            $model->phone           =   $phone;
            $model->password        =   $password;
            $model->company_name    =   $company_name;
            $model->type            =   $type;
            $model->city            =   $city;
            $model->address         =   $address;
            $model->talent          =   $talent;
            $talent_img             &&  $model->talent = 1;
            $model->business_flg    =   $business_flg;
            $business_img           &&  $model->business_flg = 1;
            $model->student_age     =   $student_age;
            $model->abroad_staff    =   $abroad_staff;
            $model->needs_num       =   $needs_num;
            $model->pay             =   $pay;
            $model->contact         =   $contact;
            $model->contact_phone   =   $contact_phone;
            $work_email             && $model->work_email      =   $work_email;

            $model->status          =   2;
            $model->gw_flg          =   2;

            $business_name          &&  $model->business_name   = $business_name;
            $talent_img             &&  $model->talent_img      = $talent_img;
            $logo                   &&  $model->logo            = $logo;
            $business_img           &&  $model->business_img    = $business_img;
            $school_img_1           &&  $model->school_img_1    = $school_img_1;
            $school_img_2           &&  $model->school_img_2    = $school_img_2;
            $model->submit_num = 1;
            if($model->save()){
                $model1 = new CompanyAdvier();
                $model1->cid    = $model->id;
                $model1->uid    = $user;
                if($model1->save()){
                    $model2 = new ImUser();
                    $model2->type = 2;
                    $model2->user_id = $model->id;
                    if($model2->save()){
                        $logo_url = config('app.url').'/logo/company_defaut_logo.png';
                        if($model->logo){
                            $logo_url = Files::where('id',$model->logo)->pluck('path')->first();
                        }
                        $res = $this->createImOneAccount(['Identifier'=>config('app.env').'_'.$model2->id,'Nick'=>$model->company_name,'FaceUrl'=> $logo_url]);
                        $res = json_decode($res,true);
                        if($res['ActionStatus'] == 'OK'){
                            //发送消息
                            $imUser = ImUser::where('user_id',$user)->where('type',3)->first();
                            $sendMsgRes = $this->sendImMsg(
                                config('app.env').'_'.$imUser->id,
                                config('app.env').'_'.$model2->id,
                                '您好，欢迎使用寰球阿帕斯，有问题可以随时咨询'
                            );
                            $sendMsgRes = json_decode($sendMsgRes,true);
                            if($sendMsgRes['ActionStatus']!='OK'){
                                DB::rollback();
                                return back()->with('error','IM消息发送失败,错误码:'.$res['ErrorCode'].' 错误描述:'.$res['ErrorInfo']);
                            }else{
                                DB::commit();
                                return redirect()->route('admin.company.add')->with('success','添加成功');
                            }
                        }else{
                            DB::rollback();
                            return back()->with('error','IM注册失败了,错误码:'.$res['ErrorCode'].' 错误描述:'.$res['ErrorInfo']);
                        }
                    }else{
                        DB::rollBack();
                        return back()->with('error','IM注册失败,请联系管理员');
                    }
                }else{
                    DB::rollBack();
                    return back()->with('error','系统错误,插入企业顾问失败');
                }
            }else{
                DB::rollBack();
                return back()->with('error','系统错误,插入企业用户失败');
            }
        }catch (\Exception $e){
            DB::rollBack();
            return back()->with('error',$e->getMessage());
        }

    }
}
