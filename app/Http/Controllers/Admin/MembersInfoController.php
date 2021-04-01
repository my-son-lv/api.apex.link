<?php

namespace App\Http\Controllers\Admin;

use App\Jobs\SendEmail;
use App\Jobs\SendWxNotice;
use App\Models\Backup;
use App\Models\Collect;
use App\Models\Companys;
use App\Models\Country;
use App\Models\Education;
use App\Models\Event;
use App\Models\Files;
use App\Models\ImUser;
use App\Models\Interview;
use App\Models\Invite;
use App\Models\Job;
use App\Models\Member;
use App\Models\MemberAdviser;
use App\Models\MemberInfo;
use App\Models\MemberInfoChecked;
use App\Models\Notice;
use App\Models\Official;
use App\Models\Region;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Image;

class MembersInfoController extends Controller
{

    public function tuijianJob(Request $request){
        $jobCheckIds = explode(',',$request->job_ids);
        $teachId = $request->teach_id;
        #获取职位列表
        $list = Job::with(['company' => function($q){
            $q->select(['id','company_name','logo']);
        }])->whereIn('id',$jobCheckIds)->get(['id','cid','name','job_type','job_city','pay','pay_unit']);
        #获取外教邮箱
        $member = Member::find($teachId);
        $data = [];
        foreach ($list as $k => $v){
            if($v->job_city){
                $v->job_area_data = Region::find($v->job_city);
                $v->job_city_data = Region::find($v->job_area_data->pid);
                $v->job_province_data  = Region::find($v->job_city_data->pid);
                $v->job_city = $v->job_city_data->pinyin .', '.$v->job_province_data->pinyin.', China';
            }else{
                $v->job_city = '';
            }
            $v->job_type =  $v->job_type == 1 ? 'Full-time, Part-time' : ($v->job_type == 2 ? 'Full-time' : 'Part-time');
            if($v->company->logo){
                $logoFile = Files::find($v->company->logo);
                $logo = $logoFile->path;
            }else{
                $logo = 'https://api.apex.link/logo/company_defaut_logo.png';
            }
            $v->pay = str_replace(',',' - ',$v->pay);
            switch ($v->pay_unit){
                case 1:
                    $v->pay_unit = '/Per Hour';break;
                case 2:
                    $v->pay_unit = '/Per Day';break;
                case 3:
                    $v->pay_unit = '/Per Week';break;
                case 4:
                    $v->pay_unit = '/Per Month';break;
                case 5:
                    $v->pay_unit = '/Per Year';break;
            }
            array_push($data,['id' =>$v->id  , 'cid' =>$v->cid, 'name' => $v->name, 'pay' => $v->pay ,'job_city' => $v->job_city,'job_type' => $v->job_type , 'logo' => $logo,'pay_unit' => $v->pay_unit ]);
        }
        $this->dispatch(new SendEmail(
            [
                'email'     => $member->email,
                'template'  => 'teach_tuijian',
                'title'     => $list[0]['company']['company_name'].' is looking for '.$list[0]['name'],
                'list'      => $data,
            ]
        ));
        return $this->success();
    }


    public function updateMemo(Request $request){
        $id = $request->get('id',0);
        $memo = $request->get('memo',1);
        if(!$id) return $this->fail(100001);
        $flg = MemberInfo::where('id',$id)->update(['memo' => $memo]);
        if($flg!==false){
            return $this->success();
        }else{
            return $this->fail();
        }
    }

    #生成教师小程序推荐码
    public function makeRecommPlaybill(Request $request)
    {
        $id = $request->get('id',10);
        $type = $request->get('type',1);
        $member_info = MemberInfo::find($id);
        $app = \EasyWeChat::miniProgram();
        $ewm_wjzl = $response = $app->app_code->get('/pages/teacherShare/main?id='.$id, [
            'width' => 330,
        ]);
        $path = public_path('tmp/'.date("YmdHis").rand(1111,99999999).'.png');
        file_put_contents($path,$ewm_wjzl);
        $img = Image::make(public_path('haibao/teach_haibao_'.$type.'.png'));
        $img->insert(file_get_contents($path), 'right-bottom', 115,111);
        unlink($path);
        //头像
        $phone = $member_info->sex==1 ? public_path('logo/teach_default_nv.png') : public_path('logo/teach_default_nan.png');
        if($member_info->photos){
            $file = Files::find($member_info->photos);
            $file && $phone = $file->path;
        }
        $img->text(str_limit($member_info->name,24,'...'), 120, 1650, function($font) {
            $font->file(public_path().'/PingFang-SC-Medium.otf');
            $font->size(54);
            $font->color('#000');
        });
        $pay = '';
        switch ($member_info->pay_type){
            case 1:
                $pay = '10K-13K';
                break;
            case 2:
                $pay = '13K-16K';
                break;
            case 3:
                $pay = '16k-20K';
                break;
            case 4:
                $pay = '20K-25K';
                break;
            case 5:
                $pay = '>25K';
                break;
        }
        $img->text($pay, 120, 1770, function($font) {
            $font->file(public_path().'/PingFang-SC-Medium.otf');
            $font->size(54);
            $font->color('#FF6010');
        });
        $county = Country::find($member_info->nationality);
        $img->text($county->code, 120, 1860, function($font) {
            $font->file(public_path().'/PingFang-SC-Medium.otf');
            $font->size(40);
            $font->color('#999999');
        });
        return $img->response("png");
    }

    #聊天推荐简历发送微信公众号通知
    public function recommednTeach(Request $request)
    {
        $id = $request->get('id','');
        $cid = $request->get('cid','');
        $cidArr = explode('_',$cid);
        $member_info = MemberInfo::find($id);
        $im_user = ImUser::find($cidArr[1]);
        $company = Companys::find($im_user->user_id);
        if($company->unionid && config('app.env') == 'production'){
            $officials = Official::where('unionid',$company->unionid)->where('status',1)->first();
            if($officials) {
                $xueli = ["High School Diploma","Associate's Degree","Bachelor's Degree","Master's Degree","MBA","PHD"];
                //发送微信通知
                $wxNoticeData = [
                    'openid' => $officials->openid,
                    'type' => 10,
                    'title' => '寰球阿帕斯为您推荐新人选,请及时处理。',
                    'memo'  => '有疑问请联系您的顾问。',
                    'page'   => 'pages/teachersDetail/main?id='.$member_info->id,
                    'key' => [
                        'keyword1' => 'ESL TEACHER',
                        'keyword2' => $member_info->name,
                        'keyword3' => $member_info->working_seniority ?($member_info->working_seniority < 2 ? $member_info->working_seniority.'年以内':
                            ($member_info->working_seniority > 9 ? $member_info->working_seniority.'年以上': $member_info->working_seniority.'年' )) : '--' ,
                        'keyword4' => $xueli[$member_info->university],
                        'keyword5' => $member_info->schoole ?? '--',
                    ],
                ];
                $this->dispatch(new SendWxNotice($wxNoticeData));
            }
        }
        return $this->success();
    }

    #顾问一句话评价外教
    public function comments(Request $request){
        $id = $request->get('id',0);
        $comments = $request->get('comments','');
        if(!$id || !$comments) return $this->fail(100001);
            $flg = MemberInfo::where('id',$id)->update(['comments' => $comments]);
        if($flg!==false){
            return $this->success();
        }else{
            return $this->fail();
        }
    }

    public function updateCategory(Request $request){
        $id = $request->get('id',0);
        $category = $request->get('category',1);
        if(!$id) return $this->fail(100001);
        $flg = MemberInfo::where('mid',$id)->update(['category' => $category]);
        if($flg!==false){
            return $this->success();
        }else{
            return $this->fail();
        }
    }

    /**
     * 显示是否可以面试  1可以 2不可以
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateSignFlg(Request $request){
        $type = $request->get('type',1);
        $id = $request->get('id',0);
        if(!$id) return $this->fail(100001);
        $flg = MemberInfo::where('mid',$id)->update(['sign_flg' => $type]);
        if($flg!==false){
            return $this->success();
        }else{
            return $this->fail();
        }
    }

    //配置外教
    public function updateAdviser(Request $request){
        $id = $request->get('id',0);
        $user = $request->get('user_id',0);
        if(!$id || !$user){
            return $this->fail(100001);
        }
        $flg1 = MemberAdviser::where('mid',$id)->update(['uid'=> $user]);
        if($flg1!==false){
            $userInfo = User::find($user);
            $memberInfo = MemberInfo::where('mid',$id)->first();
            Notice::addNotice(returnNoticeMsg(['user' => $request->user->name,'teach_name' => $memberInfo->name.' '.$memberInfo->last_name,'adviser_name' => $userInfo->name],2007),2,2007);
            Event::addEvent($request->user->name.' 变更顾问为:'.$userInfo->name ,$id,2);
            return $this->success();
        }else{
            return $this->fail();
        }

    }

    public function updateNotes(Request $request){
        $id = $request->get('id',0);
        $notes = $request->get('notes','');
        if(!$id){
            return $this->fail(100001);
        }
        $flg = MemberInfo::where('mid',$id)->first();
        if(!$flg){
            return $this->fail(100001);
        }
        DB::beginTransaction();
        try {
            $flg1 = MemberInfoChecked::where("mid",$id)->update(['notes' =>$notes]);
            $flg2 = MemberInfo::where("mid",$id)->update(['notes' =>$notes]);
            if($flg1!==false && $flg2!==false){
                $memberModel = MemberInfoChecked::where("mid",$id)->first();
                Notice::addNotice(returnNoticeMsg(['user' =>  $request->user->name ,  'teach_name' => $memberModel->name.' '.$memberModel->last_name],2003),2,2003);
                DB::commit();
                $file = Files::whereIn('id',explode(',',$notes))->get();
                return $this->success($file);
            }else{
                DB::rollBack();
                return $this->fail();
            }
        }catch (\Exception $e){
            DB::rollBack();
            return $this->fail(100000,$e->getMessage());
        }

    }

    public function add(Request $request){
        $user = $request->only('user_id')['user_id'] ?? $request->user->id;
        $data = array_filter($request->only(['email','nick_name','password']));
        $flg = Member::where('email',$data['email'])->count();
        if($flg){
            return $this->fail(100003);
        }
        $data['password'] = md5(md5('1234567890'));
        $data['user_id'] = $this->genRequestSn(rand(100000,999999));
        $data['sign_id'] = $this->genRequestSn(rand(1000000,9999999));
        $data['register_ip']  = $request->getClientIp();
        $data['register_time'] = date("Y-m-d H:i:s");
        $data_check = array_filter($request->only([
            "name",
            "sex",
            "brithday",
            "nationality",
            "abroad_address",
            "china_address",
            "school",
            "university",
            "phone",
            "wechat",
            "celta_flg",
            "celta_img",
            "cert_other_flg",
            "major",
            "working_seniority",
            "working_city",
            "desc",
            "videos",
            "photos",
            "edu_cert_flg",
            "edu_cert_imgs",
            "edu_auth_flg",
            "edu_auth_imgs",
            "work_visa_flg",
            "science_flg",
            "commit_flg",
            "work_flg",
            "work_start_time",
            "work_end_time",
            "last_name",
            "notes",
            "pay_type",
            "in_domestic",
            "visa_type",
            "country",
            "visa_exp_date",
            "school_type",
            "work_type",
            "student_age",
            "job_type",
            "job_work_type",
            "area_code",
            "comm_type",
            "cert_other",
            "cert_other_img",
            "relocate",
            "relocate",
            "all_city",
            "university_img",
        ]));

        try {
            DB::beginTransaction();
            //添加用户表
            $member = Member::create($data);
            $data_check['status'] = 2;
            $data_check['mid'] = $member->id;
            //添加审核表
            $memberCheck = MemberInfoChecked::create($data_check);
            $memberModel = MemberInfoChecked::where('mid',$member->id)->first();
            $memberModel->education()->delete();
            $memberModel->education()->createMany(json_decode($request->education,true));
            $memberModel->work()->delete();
            $memberModel->work()->createMany(json_decode($request->work_experiences, true));
            //添加正式表
            unset($data_check['status']);
            $data_check['category'] = $request->get('category',1);
            $memberInfo = MemberInfo::create($data_check);
            //绑定顾问
            $memerAdviser = MemberAdviser::create(['mid' => $member->id, 'uid' => $user]);
            //注册IM表
            $im_user = ImUser::create(['type' => 1, 'user_id' => $member->id]);
            Notice::addNotice(returnNoticeMsg(['user' => $request->user->name,'teach_name' => $memberInfo->name.' '.$memberInfo->last_name],2009),2,2009);
            //记录日志
            Event::addEvent($request->user->name.' 添加了用户',$member->id);
            if($member && $memberCheck && $memberInfo && $memerAdviser && $im_user){
                //修改昵称 导入头像
                $res = $this->createImOneAccount([
                    'Identifier'=>config('app.env').'_'.$im_user->id,
                    'Nick'=>$memberInfo->first_name.' '.$memberInfo->last_name,
                    'FaceUrl'=> $memberInfo->photos ? Files::where('id',$memberInfo->photos)->pluck('path')->first() : $this->getDefaultLogo($memberInfo->sex == '0' ? 4 : 5)[0]['path'],
                ]);
                $res = json_decode($res,true);
                if($res['ActionStatus'] != 'OK'){
                    DB::rollback();
                    Log::info('IM导入头像昵称失败了,错误码:'.$res['ErrorCode'].' 错误描述:'.$res['ErrorInfo']);
                    return $this->fail();
                }else{
                    //发送消息
                    $imUser = ImUser::where('user_id',15)->where('type',3)->first();
                    $sendMsgRes = $this->sendImMsg(
                        config('app.env').'_'.$imUser->id,
                        config('app.env').'_'.$im_user->id,
                        'Welcome to Apex Global, find a fit job and right employer.'
                    );
                    $sendMsgRes = json_decode($sendMsgRes,true);
                    if($sendMsgRes['ActionStatus']!='OK'){
                        DB::rollback();
                        Log::info('IM消息发送失败,错误码:'.$res['ErrorCode'].' 错误描述:'.$res['ErrorInfo']);
                        return $this->fail();
                    }else{
                       /* $emailData['teach_name'] = $data_check['name'].' '.$data_check['last_name'];
                        $email = $data['email'];
                        Mail::send('email.add_member',['emailData' => $emailData],function($message)use($email){
                            $message ->to($email)->subject('寰球阿帕斯');
                        });*/

                        $this->dispatch(new \App\Jobs\JobMate(['mid' => $memberCheck->mid , 'type' => 1 ]));
                        DB::commit();
                        return $this->success();
                    }
                }
            }
        }catch (\Exception $e){
            DB::rollBack();
            return $this->fail(100000,$e->getMessage());
        }
    }

    public function edit1(Request $request){
        $id = $request->get('id',0);
        if(!$id){
            return $this->fail(100001);
        }
        $user = $request->only('user_id');
        $data = array_filter($request->only(['email','nick_name']));
        $flg = Member::where('email',$data['email'])->where('id','<>',$id)->count();
        if($flg){
            return $this->fail(100003);
        }

        $data_check = array_filter($request->only([
            "name",
            "sex",
            "brithday",
            "nationality",
            "abroad_address",
            "china_address",
            "school",
            "university",
            "phone",
            "wechat",
            "celta_flg",
            "celta_img",
            "cert_other_flg",
            "major",
            "working_seniority",
            "working_city",
            "desc",
            "edu_cert_flg",
            "edu_cert_imgs",
            "edu_auth_flg",
            "edu_auth_imgs",
            "work_visa_flg",
            "science_flg",
            "commit_flg",
            "work_flg",
            "work_start_time",
            "work_end_time",
            "last_name",
            "notes",
            "pay_type",
            "in_domestic",
            "visa_type",
            "country",
            "visa_exp_date",
            "school_type",
            "work_type",
            "student_age",
            "job_type",
            "job_work_type",
            "area_code",
            "comm_type",
            "cert_other",
            "cert_other_img",
            "relocate",
            "relocate",
            "all_city",
            "university_img",
        ]));
        $data_check['videos'] = $request->videos ?? '' ;
        $data_check['photos'] = $request->photos ?? '';

        try {
            DB::beginTransaction();
            //修改用户表
            $member = Member::find($id);
            $flg1 = $member->update($data);
            //修改审核表
            $flg2 = MemberInfoChecked::where('mid',$id)->update($data_check);
            $memberModel = MemberInfoChecked::where('mid',$id)->first();
            $memberModel->education()->delete();
            $memberModel->education()->createMany(json_decode($request->education,true));
            $memberModel->work()->delete();
            $memberModel->work()->createMany(json_decode($request->work_experiences, true));
            //修改正式表
            $data_check['category'] = $request->get('category',1);
            $flg3 = MemberInfo::where('mid',$id)->update($data_check);
            //绑定顾问
            $flg4 = MemberAdviser::where('mid',$id)->update(['uid' => isset($user['user_id']) ? $user['user_id'] : $request->user->id ]);
            if($flg1!==false && $flg2!==false  && $flg3!==false && $flg4!==false ){
                Event::addEvent($request->user->name.' 修改了用户信息',$member->id);
                Notice::addNotice(returnNoticeMsg(['user' =>  $request->user->name ,  'teach_name' => $memberModel->name.' '.$memberModel->last_name],2003),2,2003);
                $im_user = ImUser::where('user_id',$id)->where('type',1)->first();
                $memberInfo = MemberInfo::where('mid',$id)->first();
                //修改昵称 导入头像
                $res = $this->createImOneAccount([
                    'Identifier'=>config('app.env').'_'.$im_user->id,
                    'Nick'=>$memberInfo->first_name.' '.$memberInfo->last_name,
                    'FaceUrl'=> $memberInfo->photos ? Files::where('id',$memberInfo->photos)->pluck('path')->first() : $this->getDefaultLogo($memberInfo->sex == '0' ? 4 : 5)[0]['path'],
                ]);
                $res = json_decode($res,true);
                if($res['ActionStatus'] != 'OK'){
                    DB::rollback();
                    Log::info('IM导入头像昵称失败了,错误码:'.$res['ErrorCode'].' 错误描述:'.$res['ErrorInfo']);
                    return $this->fail();
                }else{
                    $this->dispatch(new \App\Jobs\JobMate(['mid' => $memberModel->mid , 'type' => 1 ]));
                    DB::commit();
                    return $this->success();
                }
            }
        }catch (\Exception $e){
            DB::rollBack();
            return $this->fail(100000,$e->getMessage());
        }
    }

    public function delete(Request $request){
        $id = $request->get('id',0);
        if(!$id){
            return $this->fail(100003);
        }
        $member = Member::find($id);
        if(!$member){
            return $this->fail(100003);
        }
        $memberInfo = MemberInfo::where('mid',$id)->first();
        $memberInfo->email = $member->email;
        $memberInfo->password = $member->password;
        $memberInfo->nick_name = $member->nick_name;
        $memberInfo->user_id = $member->user_id;
        $memberInfo->sign_id = $member->sign_id;
        $memberInfo->invite_code = $member->invite_code;

        DB::beginTransaction();
        try{
            //备份企业用户数据
            $add_flg = Backup::create(['json' => json_encode($memberInfo), 'type' => 2,'user_id' => $request->user->id]);
            //删除member表
            $flg1 = Member::where('id',$id)->delete();
            //删除审核表
            $flg2 = MemberInfoChecked::where("mid",$id)->delete();
            //删除正式表
            $flg3 = MemberInfo::where("mid",$id)->delete();
            //删除im表
            $flg4 = ImUser::where('user_id',$id)->where('type',1)->delete();
            //删除面试表
            $flg5 = Interview::where('mid',$id)->delete();
            //删除收藏表
            $flg6 = Collect::where('mid',$id)->delete();
            //删除顾问表
            $flg7 = MemberAdviser::where('mid',$id)->delete();
            DB::commit();
            return $this->success();
        }catch (\Exception $e){
            DB::rollback();
            return $this->fail(100000,$e->getMessage());
        }

    }



    /**
     * 外交列表管理
     * @return MemberController
     */
    public function checkList(){
        $name       = Input::get('name','');
        $email      = Input::get('email','');
        $phone      = Input::get('phone','');
        $passport   = Input::get('passport','');
        $nationality= Input::get('nationality','');//国籍
        $working_seniority  = Input::get('working_seniority','');//工作年限
        $visa_type      = Input::get('work_visa_flg','');//签证 1无 2有
        $working_city       = Input::get('working_city','');//期望工作地
        $advert  = Input::get('advert',0);
        $work_flg   = Input::get('work_flg',0);
        $page   = Input::get('page',1);
        $pageSize = Input::get('pageSize',config('admin.pageSize'));
        $language_flg   = Input::get('language_flg',0);//0全部  1母语 2非母语
        $in_domestic   = Input::get('in_domestic',0);//1否 2是
        if($page<1) $page = 1;
        $list = MemberInfo::from('members_info as a')
            ->leftjoin('members as b','a.mid','=','b.id')
            ->leftjoin('member_advisers as c','a.mid','=','c.mid');
        if($name){
            $list = $list->where('a.name','like','%'.$name.'%');
        }
        if($email){
            $list = $list->where('b.email','like','%'.$email.'%');
        }
        if($work_flg){
            $list = $list->where('a.work_flg',$work_flg==1 ? 0 : 1);
        }
        if($phone){
            $list = $list->where('a.phone','like','%'.$phone.'%');
        }
        if($passport){
            $list = $list->where('a.passport','like','%'.$passport.'%');
        }
        if($language_flg){
            $guoji = [];
            if($language_flg == 1){
                $guoji = Country::where('flg',1)->get(['id']);
            }else{
                $guoji = Country::where('flg',0)->get(['id']);
            }
            $list = $list->whereIn('a.nationality',$guoji);
        }
        if($nationality){
            $list = $list->where('a.nationality',$nationality);
        }
        if($working_seniority){
            $list = $list->where('a.working_seniority', $working_seniority);
        }
        if($visa_type == 2 ){
            $list = $list->where('a.visa_type','1');
        }
        if($in_domestic){
            $list = $list->where('a.in_domestic',$in_domestic - 1 );
        }
        if($working_city){
            $city = Region::find($working_city);
            $arr1 = [];
            array_push($arr1,$working_city);
            if($city->level == 1){
                //查询市
                $city_list = Region::where('pid',$city->id)->get();
                foreach ($city_list as $k => $v){
                    array_push($arr1,$v->id);
                }
            }
            $list = $list->whereIn('a.working_city',$arr1);
        }
        if($advert){
            $list = $list->where('c.uid',$advert);
        }
        $count = ceil($list->count()/$pageSize);
        $list = $list->orderBy('a.id','desc')->offset(($page-1)*$pageSize)->limit($pageSize)->get(['a.*','b.email','b.nick_name','b.invite_code','c.uid','a.id as id']);
        foreach ($list as $k => $v){
            $country =  Country::find($v['nationality']);
            $list[$k]['nationality_val'] = $country['code'];
            $v->invite = [];
            if($v->invite_code){
                $v->invite = Invite::where('code',$v->invite_code)->first(['name', 'phone','email']);
            }
            $v->updated_at = $v->created_at;
        }
        return $this->success(['count' => $count,'list' => $list,'page' => $page]);
    }

    /**
     * 外教详情查看
     * @return MemberController
     */
        public function view(){
        $id     = Input::get('id',0);
        if(!$id){
            return $this->fail(100001);
        }
        $model = MemberInfo::with([
            'member' => function($query){
                $query->select(['id','email','nick_name','user_id','sign_id','created_at']);
            },
            'education',
            'work',
            'nationality_val',
            'videos_path',
            'photos_path',
            'edu_cert_imgs_path',
            'edu_auth_imgs_path',
            'celta_img_path',
            'cert_other_img_path',
            'country_val',
            'university_img_path'
        ])->where('id', $id)->first();
        @$model->working_city_datas = null;
        if ($model->working_city) {
            $city_arr = explode(',', $model->working_city);
            $citys = [];
            foreach ($city_arr as $k => $v) {
                $tmp_city = Region::find($v);
                $tmp_pro = Region::find($tmp_city->pid);
                $citys[] = [
                    'province_data' => $tmp_pro,
                    'city_data' => $tmp_city,
                ];
            }
            $model->working_city_datas = $citys;
        }
        $model->china_address_city_data = null;
        if ($model->china_address) {
            $tmp_city = Region::find($model->china_address);
            $tmp_pro = Region::find($tmp_city->pid);
            $model->china_address_city_data = [
                'province_data' => $tmp_pro,
                'city_data' => $tmp_city,
            ];
        }
        $model->notes_path = null;
        if ($model->notes) {
            $model->notes_path = Files::whereIn('id', explode(',', $model->notes))->get();
        }
        return $this->success($model);
        /*$data = MemberInfo::with(['education'])->where('id',$id)->first();
        if(!$data){
            return $this->fail(2000005);
        }else{
            $member = Member::find($data['mid']);
            $data->nick_name = $member->nick_name;
            $data->email = $member->email;
            $data->user_id = $member->user_id;
            $data->sign_id = $member->sign_id;
            $data->invite_code  = $member->invite_code;
            $country =  Country::find($data->nationality);
            $data['nationality_val'] = $country['code'];
            $data->videos_path = null;
            if($data->videos){
                $data->videos_path = Files::whereIn('id',explode(',',$data->videos))->get();
            }
            if($data->photos){
                $data->photos_path = Files::whereIn('id',explode(',',$data->photos))->get();
            }
            $data->working_city_datas = null;
            if($data->working_city){
                $city_arr = explode(',',$data->working_city);
                $citys = [];
                foreach ($city_arr as $k1 =>$v1){
                    $tmp_city = Region::find($v1);
                    $tmp_pro  = Region::find($tmp_city->pid);
                    $citys[] = [
                        'province_data'=> $tmp_pro,
                        'city_data'    => $tmp_city,
                    ];
                }
                $data->working_city_datas = $citys;
            }
            $data->china_address_city_data = null;
            if($data->china_address && $data->in_domestic == 1){
                $tmp_city = Region::find($data->china_address);
                $tmp_pro  = Region::find($tmp_city->pid);
                $data->china_address_city_data = [
                    'province_data'=> $tmp_pro,
                    'city_data'    => $tmp_city,
                ];
            }
            $data->edu_cert_imgs_path = null;
            if($data->edu_cert_imgs){
                $data->edu_cert_imgs_path = Files::whereIn('id',explode(',',$data->edu_cert_imgs))->get();
            }
            $data->edu_auth_imgs_path = null;
            if($data->edu_auth_imgs){
                $data->edu_auth_imgs_path = Files::whereIn('id',explode(',',$data->edu_auth_imgs))->get();
            }
            $data->notes_path = null;
            if($data->notes){
                $data->notes_path = Files::whereIn('id',explode(',',$data->notes))->get();
            }
            $data->invite = [];
            if($data->invite_code){
                $data->invite = Invite::where('code',$data->invite_code)->first(['name', 'phone','email']);
            }
            return $this->success($data);
        }*/
    }


//    public function edit(Request $request){
//        $data  = Input::all();
//        if(!$data['id']){
//            return $this->fail(100001);
//        }
//        $id = $data['id'];
//        unset($data['id']);
//        unset($data['token']);
//        $user = $request->user;
//        unset($data['user']);
//        if(count($data) < 1){
//            return $this->fail(100001);
//        }
//        try{
//            DB::beginTransaction();
//            //查出用户给mid
//            $model = MemberInfo::where('id',$id)->first();
//            //修改正式表
//            $flg    = MemberInfo::where('id',$id)->update($data);
//            //修改草稿表
//            $flg1   = MemberInfoChecked::where('mid',$model->id)->update($data);
//            if($flg!==false && $flg1!==false){
//                $memberModel = MemberInfoChecked::where("mid",$model->id)->first();
//                Notice::addNotice(returnNoticeMsg(['user' =>  $user->name ,  'teach_name' => $memberModel->name.' '.$memberModel->last_name],2003),2,2003);
//                DB::commit();
//                return $this->success();
//            }else{
//                DB::rollback();
//                return $this->fail();
//            }
//        }catch (\Exception $e){
//            Log::info('编辑出错了：'.$e->getMessage());
//            DB::rollback();
//            return $this->fail();
//        }
//    }

}
