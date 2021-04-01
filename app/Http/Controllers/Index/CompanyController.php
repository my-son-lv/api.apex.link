<?php

namespace App\Http\Controllers\Index;

use App\Models\Code;
use App\Models\Collect;
use App\Models\CompanyAdvier;
use App\Models\CompanyCheckLog;
use App\Models\Companys;
use App\Models\CompanyViewLog;
use App\Models\Country;
use App\Models\Down;
use App\Models\Event;
use App\Models\Files;
use App\Models\ImUser;
use App\Models\Interview;
use App\Models\Job;
use App\Models\Member;
use App\Models\MemberInfo;
use App\Models\Notice;
use App\Models\Region;
use App\Models\User;
use App\Models\Vip;
use App\Models\VipAction;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Log;
use Mrgoon\AliSms\AliSms;
use function foo\func;

class CompanyController extends Controller
{
    #授权解密绑定账号
    public function wxAuthInfo(Request $request)
    {
        if (!$request->code || !$request->iv && !$request->encryptedData) {
            return $this->fail(100001);
        }
        $miniProgram = \EasyWeChat::miniProgram();
        $wx_data = $miniProgram->auth->session($request->code);
        Log::info('小程序CODE解密:' . json_encode($wx_data));
        //{"session_key":"\/tVEJPZhndOumUm5sUlt5w==","openid":"oxqLd4sQI6Q7xie2OJYS1wADaxrs","unionid":"oQEcTwjxSZOXAyyl0m89Y3_slxMQ"}
        if (!isset($wx_data['errcode'])) {
            $decryptedData = $miniProgram->encryptor->decryptData($wx_data['session_key'], $request->iv, $request->encryptedData);
            Log::info('用户信息解密:' . json_encode($decryptedData));
            //{"openId":"oxqLd4sQI6Q7xie2OJYS1wADaxrs","nickName":"\u9f99","gender":1,"language":"zh_CN","city":"","province":"","country":"Oman","avatarUrl":"https:\/\/thirdwx.qlogo.cn\/mmopen\/vi_32\/Q0j4TwGTfTLj7VflgqBVb3IQRKZbMoJU0WOuZUvDIia9icscYvbbQuyKqDXNd3k7rSy45Heqq3DFg8KauKicdJfzw\/132","unionId":"oQEcTwjxSZOXAyyl0m89Y3_slxMQ","watermark":{"timestamp":1609125211,"appid":"wx5d1d80ada0d2bb95"}}
            //通过openId查找用户修改用户 unionId
            $company = Companys::where('open_id', $decryptedData['openId'])->first();
            $company->unionid = $decryptedData['unionId'];
            $company->save();
            return $this->success();
        } else {
            Log::info('小程序CODE解密失败了');
            return $this->fail();
        }
    }


    #下载简历历史
    public function downList(Request $request)
    {
        $page = $request->get('page', 1);
        $pageSize = $request->get('pageSize', 15);
        $list = Down::from('downs AS a')
            ->join('members_info AS b', 'b.mid', '=', 'a.mid')
            ->where('cid', $request->company->id)
            ->orderBy('a.id', 'desc');
        $total = $list->count();
        $count = ceil($total / $pageSize);
        $list = $list->orderBy('a.id', 'desc')
            ->offset(($page - 1) * $pageSize)
            ->limit($pageSize)
            ->get([
                'b.name',
                'b.last_name',
                'b.working_seniority',
                'b.id',
                'a.mid',
                'pay_type',
                'a.created_at',
                'b.photos',
                'b.desc',
                'b.university',
                'b.category',
                'nationality',
                'b.sex',
            ]);
        foreach ($list as $k => $v) {
            if ($v->photos) {
                $v->photos_path = Files::whereIn('id', explode(',', $v->photos))->get();
            }
            $country = Country::find($v->nationality);
            $v->nationality_val = $country['code'];
        }
        return $this->success(['count' => $count, 'total' => $total, 'list' => $list]);
    }

    #获取会员列表
    public function getVipList(Request $request)
    {
        return $this->success(Vip::where('status', 1)->where('show', 1)->get());
    }

    #下载简历
    public function downNotes(Request $request)
    {
        $id = $request->get('id', '');
        //判断是否优质外教
        $member_category = MemberInfo::where('mid', $id)->value('category');
        if ($member_category == 2) {
            return $this->fail(2000211);
        }
        $vipAction = VipAction::find($request->company->vip_actions_id);
        //判断是否下载过
        $count = Down::where('mid', $id)->where('cid', $request->company->id)->count();
        if (!$count) {
            if ($request->company->vip_actions_id) {//会员
                //判断是否是否超过次数
                $vip = Vip::find($vipAction->vip_id);
                if ($vipAction->yy_down >= ($vip->down)) {
                    return $this->fail(2000213);
                } else {
                    //下载次数加1
                    VipAction::where(['id' => $request->company->vip_actions_id])->increment('yy_down');
                    //下载简历记录
                    Down::create(['mid' => $id, 'cid' => $request->company->id, 'vip_id' => $vipAction->yy_down == $vip->down ? null : $request->company->vip_actions_id]);
                }
            } else {//不是会员
                return $this->fail(2000213);
                //判断是否用过免费的1次
                /*$count1 = Down::where('cid',$request->company->id)->whereNull('vip_id')->count();
                if($count1){
                    return $this->fail(2000213);
                }else{
                    //下载简历记录
                    Down::create(['mid' => $id , 'cid' => $request->company->id]);
                }*/
            }
        }
        $data = MemberInfo::with([
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
        ])->where('mid', $id)->first();
        $member = Member::find($id);
        @$data->interview_status = null;
        $data->collect_flg = 0;
        $ms = Interview::where('cid', $request->company->id)->where('mid', $data->mid)->orderBy('id', 'desc')->first();
        if ($ms) {
            $data->interview_status = $ms->status;
        }
        $collect_flg = Collect::where('mid', $member->id)->where('cid', $request->company->id)->where('type', 1)->count();
        $data->collect_flg = $collect_flg ? 1 : 0;
        $data->nick_name = $member->nick_name;
        $data->email = $member->email;
        $data->user_id = $member->user_id;
        $data->sign_id = $member->sign_id;
//        $country =  Country::find($data->nationality);
//        $data['nationality_val'] = $country['code'];
//        $data->videos_path = null;
//        if($data->videos){
//            $data->videos_path = Files::whereIn('id',explode(',',$data->videos))->get();
//        }
//        if($data->photos){
//            $data->photos_path = Files::whereIn('id',explode(',',$data->photos))->get();
//        }
        @$data->working_city_datas = null;
        if ($data->working_city) {
            $city_arr = explode(',', $data->working_city);
            $citys = [];
            foreach ($city_arr as $k1 => $v1) {
                $tmp_city = Region::find($v1);
                $tmp_pro = Region::find($tmp_city->pid);
                $citys[] = [
                    'province_data' => $tmp_pro,
                    'city_data' => $tmp_city,
                ];
            }
            $data->working_city_datas = $citys;
        }
        @$data->china_address_city_data = null;
        if ($data->china_address && $data->in_domestic == 1) {
            $tmp_city = Region::find($data->china_address);
            $tmp_pro = Region::find($tmp_city->pid);
            $data->china_address_city_data = [
                'province_data' => $tmp_pro,
                'city_data' => $tmp_city,
            ];
        }
        /*$data->edu_cert_imgs_path = null;
        if($data->edu_cert_imgs){
            $data->edu_cert_imgs_path = Files::whereIn('id',explode(',',$data->edu_cert_imgs))->get();
        }
        $data->edu_auth_imgs_path = null;
        if($data->edu_auth_imgs){
            $data->edu_auth_imgs_path = Files::whereIn('id',explode(',',$data->edu_auth_imgs))->get();
        }*/
        @$data->notes_path = null;
        if ($member_category == 2) {
            $data->email = null;
            $data->wechat = null;
            $data->phone = null;
        }
        return $this->success($data);
    }

    public function updateLogo(Request $request)
    {
        DB::beginTransaction();
        try {
            $logo = $request->get('logo', '');
            if (!$logo) {
                return $this->fail(100001);
            }
            $request->company->logo = $logo;
            if ($request->company->save()) {
                Event::addEvent('修改了个人信息', $request->company->id, 2);
                $logo_path = Files::where('id', $request->company->logo)->pluck('path')->first();
                $im_user_id = ImUser::where('type', 2)->where('user_id', $request->company->id)->first();
                $res = $this->createImOneAccount(['Identifier' => config('app.env') . '_' . $im_user_id->id, 'Nick' => $request->company->company_name, 'FaceUrl' => $logo_path]);
                $res = json_decode($res, true);
                if ($res['ActionStatus'] != 'OK') {
                    DB::rollback();
                    Log::info('IM注册失败了,错误码:' . $res['ErrorCode'] . ' 错误描述:' . $res['ErrorInfo']);
                    return $this->fail();
                }
                DB::commit();
                $log = CompanyCheckLog::find($request->company->check_log_id);
                if ($log) {
                    $request->company->apply_time = $log->submit_time;
                } else {
                    $request->company->apply_time = '';
                }
                if ($request->company->gw_flg) {
                    $ad_list = CompanyAdvier::where('cid', $request->company->id)->get(['uid']);
                    $userList = User::whereIn('id', $ad_list)->get(['id', 'name']);
                    $request->company->adviser = $userList;
                } else {
                    $request->company->adviser = [];
                }

                if ($request->company->talent == 1) {
                    $request->company->talent_img_path = Files::whereIn('id', explode(',', $request->company->talent_img))->get();
                } else {
                    $request->company->talent_img_path = [];
                }
                $request->company->business_img_path = Files::whereIn('id', explode(',', $request->company->business_img))->get();
                $job_list = Job::where('cid', $request->company->id)->orderBy('id', 'desc')->get(['id', 'name']);
                $request->company->job_list = $job_list;
                if ($request->company->school_img_1) {
                    $request->company->school_img_1_path = Files::find($request->company->school_img_1);
                } else {
                    @$request->company->school_img_1_path = null;
                }
                if ($request->company->school_img_2) {
                    $request->company->school_img_2_path = Files::find($request->company->school_img_2);
                } else {
                    @$request->company->school_img_2_path = null;
                }
                if ($request->company->logo) {
                    $request->company->logo_path = Files::find($request->company->logo);
                } else {
                    $request->company->logo_path = ['path' => config('app.url') . '/logo/company_defaut_logo.png'];
                }

                return $this->success($request->company);
            } else {
                DB::rollBack();
                return $this->fail();
            }
        } catch (\Exception $e) {
            DB::rollBack();
//            echo "出错了:".$e->getMessage();
            return $this->fail();
        }
    }

    public function getComapnyDesc(Request $request)
    {
        unset($request->company->password);
        unset($request->company->token);
        unset($request->company->token_expire_time);
        if ($request->company->city) {
            $city = Region::find($request->company->city);
        }
        $request->company->city_name = $request->company->city ? $city : '';
        $log = CompanyCheckLog::find($request->company->check_log_id);
        if ($log) {
            $request->company->apply_time = $log->submit_time;
        } else {
            $request->company->apply_time = '';
        }
        if ($request->company->gw_flg) {
            $ad_list = CompanyAdvier::where('cid', $request->company->id)->get(['uid']);
            $userList = User::whereIn('id', $ad_list)->get(['id', 'name']);
            $request->company->adviser = $userList;
        } else {
            $request->company->adviser = [];
        }

        if ($request->company->talent == 1) {
            $request->company->talent_img_path = Files::whereIn('id', explode(',', $request->company->talent_img))->get();
        } else {
            $request->company->talent_img_path = [];
        }
        $request->company->business_img_path = Files::whereIn('id', explode(',', $request->company->business_img))->get();
        $job_list = Job::where('cid', $request->company->id)->orderBy('id', 'desc')->get(['id', 'name']);
        $request->company->job_list = $job_list;
        if ($request->company->school_img_1) {
            $request->company->school_img_1_path = Files::find($request->company->school_img_1);
        } else {
            @$request->company->school_img_1_path = null;
        }
        if ($request->company->school_img_2) {
            $request->company->school_img_2_path = Files::find($request->company->school_img_2);
        } else {
            @$request->company->school_img_2_path = null;
        }
        if ($request->company->logo) {
            $request->company->logo_path = Files::find($request->company->logo);
        } else {
            $request->company->logo_path = ['path' => config('app.url') . '/logo/company_defaut_logo.png'];
        }

        return $this->success($request->company);
    }

    /**
     * 校验密码是否正确
     * @param Request $request
     * @return CompanyController
     */
    public function checkPassword(Request $request)
    {
        $password = $request->get('password', '');
        if (!$password) {
            return $this->fail(100001);
        }
        if (md5(md5($password)) == $request->company->password) {
            return $this->success(['check_password' => true]);
        } else {
            return $this->success(['check_password' => false]);
        }
    }

    /**
     * 发送短信验证码
     * @param Request $request
     * @return CompanyController
     */
    public function sendCheckSms(Request $request)
    {
        $phone = Input::get('phone', '');
        if (!$phone) {
            return $this->fail(100001);
        }
        $code = mt_rand(100000, 999999);
        $data = Code::where('email', $phone)->orderBy('id', 'desc')->first();
        if ($data && strtotime($data->created_at) + 60 > time()) {
            return $this->fail(100007);
        }
        try {
            DB::beginTransaction();
            $sms_send_flg = $this->aliyunSendSms($phone, 'SMS_205315358', $code);
            Code::addCode($phone, $code);
            if ($sms_send_flg['status'] == true) {
                DB::commit();
                return $this->success();
            } else {
                DB::rollback();
                return $this->fail(100000, $sms_send_flg['msg']);
            }
        } catch (\Exception $exception) {
            DB::rollback();
            Log::info($exception->getMessage());
            return $this->fail();
        }
    }

    /**
     * 通过短信验证码修改手机号
     * @param Request $request
     * @return CompanyController
     */
    public function updatePhoneByCode(Request $request)
    {
        $phone = $request->get('phone', '');
        $code = $request->get('code', '');
        if (!$code || !$phone) {
            return $this->fail(100001);
        }
        $flg = Companys::where('phone', $phone)->where('id', '<>', $request->company->id)->count();
        if ($flg) {
            return $this->fail(100003);
        }
        $codeData = Code::where('email', $phone)->orderBy('id', 'desc')->first();
        if (!$codeData) {
            return $this->fail('100005');
        }
        if (strtotime($codeData->created_at) + 300 < time()) {
            return $this->fail('100006');
        }
        if ($code != $codeData->code) {
            return $this->fail('100002');
        }
        $request->company->phone = $phone;
        if ($request->company->save()) {
            Code::delCode($phone);
            return $this->success();
        } else {
            return $this->fail();
        }
    }


    /**
     * 获取公司顾问在IM中的账号ID
     *
     * @param Request $request
     * @return CompanyController
     */
    public function getImAdviser(Request $request)
    {
        $token = $request->get('token', '');
        $company = Companys::where('token', $token)->first();
        $adviser = CompanyAdvier::where('cid', $company->id)->first();
        if ($adviser) {
            $im = ImUser::where('type', 3)->where('user_id', $adviser->uid)->first();
            $user = User::find($adviser->uid);
        }
        return $this->success(['im_user_id' => $adviser ? $im->id : null, 'name' => $adviser ? $user->name : '']);
    }

    /**
     * 外教详情
     * @param Request $request
     * @return CompanyController
     */
    public function teachDesc(Request $request)
    {
        $id = $request->get('id', 0);
        $token = $request->get('token', '');
        @$company = null;
        if ($token) {
            $company = Companys::where('token', $token)->first();
        }

        if (!$id) {
            return $this->fail(100001);
        }
        $userinfo = MemberInfo::where('id',$id)->first();
        $click=$userinfo['click_count']+1;
        MemberInfo::where('id',$id)->update(['click_count'=>$click]);
        // dd($pv);
        $data = MemberInfo::with([
            'education',
            'work' => function ($q) {
                $q->limit(1);
            },
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
        if (!$data) {
            return $this->fail(2000005);
        } else {
            if($data->education){
                foreach ($data->education as $k => $v){
                    if($v->edu_start_time){
                        $v->edu_start_time = str_replace('-','/',$v->edu_start_time);
                    }
                    if($v->edu_end_time){
                        $v->edu_end_time = str_replace('-','/',$v->edu_end_time);
                    }
                }
            }
            if($data->work){
                foreach ($data->work as $k => $v){
                    if($v->start_time){
                        $v->start_time = str_replace('-','/',$v->start_time);
                    }
                    if($v->end_time){
                        $v->end_time = str_replace('-','/',$v->end_time);
                    }
                }
            }

            if ($data->work && isset($data->work[0])) {
                $data->work[0]->work_desc = mb_strlen($data->work[0]->work_desc) > 200 ? mb_substr($data->work[0]->work_desc, 0, 200) . "..." : $data->work[0]->work_desc;
            }
            $member = Member::find($data['mid']);
            @$data->interview_status = null;
            $data->collect_flg = 0;
            if ($company) {
                $ms = Interview::where('cid', $company->id)->where('mid', $data->mid)->orderBy('id', 'desc')->first();
                if ($ms) {
                    $data->interview_status = $ms->status;
                }
                $collect_flg = Collect::where('mid', $member->id)->where('cid', $company->id)->where('type', 1)->count();
                $data->collect_flg = $collect_flg ? 1 : 0;
                //插入浏览记录
                $viewLogModel = new CompanyViewLog();
                $viewLogModel->cid = $company->id;
                $viewLogModel->mid = $data['mid'];
                if (!$viewLogModel->save()) {
                    Log::info('浏览记录保存出错了');
                }
            }
            $data->nick_name = $member->nick_name;
            $data->email = $member->email;
            $data->user_id = $member->user_id;
            $data->sign_id = $member->sign_id;
            $country = Country::find($data->nationality);
            $data['nationality_val'] = $country['code'];
            /*$data->videos_path = null;
            if($data->videos){
                $data->videos_path = Files::whereIn('id',explode(',',$data->videos))->get();
            }
            if($data->photos){
                $data->photos_path = Files::whereIn('id',explode(',',$data->photos))->get();
            }*/
            @$data->working_city_datas = null;
            if ($data->working_city) {
                $city_arr = explode(',', $data->working_city);
                $citys = [];
                foreach ($city_arr as $k1 => $v1) {
                    $tmp_city = Region::find($v1);
                    $tmp_pro = Region::find($tmp_city->pid);
                    $citys[] = [
                        'province_data' => $tmp_pro,
                        'city_data' => $tmp_city,
                    ];
                }
                @$data->working_city_datas = $citys;
            }
            @$data->china_address_city_data = null;
            if ($data->china_address && $data->in_domestic == 1) {
                $tmp_city = Region::find($data->china_address);
                $tmp_pro = Region::find($tmp_city->pid);
                $data->china_address_city_data = [
                    'province_data' => $tmp_pro,
                    'city_data' => $tmp_city,
                ];
            }
            /*$data->edu_cert_imgs_path = null;
            if($data->edu_cert_imgs){
                $data->edu_cert_imgs_path = Files::whereIn('id',explode(',',$data->edu_cert_imgs))->get();
            }
            $data->edu_auth_imgs_path = null;
            if($data->edu_auth_imgs){
                $data->edu_auth_imgs_path = Files::whereIn('id',explode(',',$data->edu_auth_imgs))->get();
            }*/
            @$data->notes_path = null;
            //判断是否优质外教
            $member_category = MemberInfo::where('mid', $data['mid'])->value('category');
            if ($member_category == 2 || !$token) {
                $data->email = null;
                $data->wechat = null;
                $data->phone = null;
            } else {
                //判断是否下载过
                $count = Down::where('mid', $data['mid'])->where('cid', $company->id)->count();
                if (!$count) {
                    $data->email = null;
                    $data->wechat = null;
                    $data->phone = null;
                }
            }
            return $this->success($data);
        }

    }

    /**
     * 搜索外教接口
     * @param Request $request
     * @return CompanyController
     */
    public function searchTeach(Request $request)
    {

        $page = Input::get('page', 1);
        $pageSize = Input::get('pageSize', config('admin.pageSize'));
        $type = $request->get('type', 1);//1推荐  2热门  3最新
        $token = $request->get('token', '');

//        $language_flg = $request->get('language_flg', 0);//0全部  1母语 2非母语
//        $nationality = Input::get('nationality', '');//国籍
//        $pay_type = $request->get('pay_type', 0);//薪资
//        $degree = $request->get('degree', 0);//学历
//        $seniority = $request->get('seniority', 0);//年限

        $company = '';
        if ($token) {
            $company = Companys::where('token', $token)->first();
        }
        if ($page < 1) $page = 1;
        DB::connection()->enableQueryLog();
        $list = MemberInfo::from('members_info as a')
            ->leftjoin('members as b', 'a.mid', '=', 'b.id')
            ->leftjoin('countrys as c', 'a.nationality', '=', 'c.id');

        $list = $list->where(function ($query) use ($request) {
            if ($request->nationality) {
                $query->Orwhere('a.nationality', $request->nationality);
            } else {
                if ($request->language_flg) {
                    $query->Orwhere('c.flg', $request->language_flg == 1 ? 1 : 0);
                }
            }
            $request->pay_type && $query->Orwhere('a.pay_type', $request->pay_type);
            switch ($request->degree) {
                case 1:
                    $query->OrWhereIn('a.university', [3, 4, 5, 6]);
                    break;
                case 2:
                    $query->OrWhereIn('a.university', [4, 5, 6]);
                    break;
                case 3:
                    $query->OrWhereIn('a.university', [6]);
                    break;
            }
            switch ($request->seniority) {
                case 1:
                    $query->OrWhereIn('a.working_seniority', [1]);
                    break;
                case 2:
                    $query->OrWhereIn('a.working_seniority', [2, 3, 4]);
                    break;
                case 3:
                    $query->OrWhereIn('a.working_seniority', [4, 5, 6]);
                    break;
                case 4:
                    $query->OrWhereIn('a.working_seniority', [7, 8, 9, 10]);
                    break;
                case 5:
                    $query->OrWhereIn('a.working_seniority', [11]);
                    break;
            }
        });
        $total = $list->count();
        $count = ceil($total / $pageSize);
        switch ($type) {
            case 1:
//                $list = $list->orderBy('a.id','asc');
                $list = $list->orderBy('c.flg', 'desc')->orderBy('a.university', 'desc')->orderBy('a.videos', 'desc')->orderBy('a.id', 'desc');
                break;
            case 2:
                $list = $list->orderBy('a.hot', 'desc')->orderBy('a.id', 'desc');
                break;
            case 3:
                $list = $list->orderBy('a.id', 'desc');
                break;
        }
        $list = $list->offset(($page - 1) * $pageSize)->limit($pageSize)->get(['a.*', 'b.email', 'b.nick_name', 'c.flg']);
        $view_num = 0;
        if ($company) {
            $view_num = CompanyViewLog::where('cid', $company->id)->GroupBy('mid')->get()->count();
        }
        foreach ($list as $k => $v) {
            $country = Country::find($v['nationality']);
            $list[$k]['nationality_val'] = $country['code'];
            @$list[$k]['country_val'] = null;
            if ($list[$k]['country']) {
                $country = Country::find($v['country']);
                $list[$k]['country_val'] = $country['code'];
            }
//            $list[$k]['photos_path'] = Files::whereIn('id',explode(',',$v->photos))->get();
            if ($v->photos) {
                $list[$k]['photos_path'] = Files::whereIn('id', explode(',', $v->photos))->get();
            }
            $list[$k]['view_num'] = $view_num;
            $list[$k]['view_flg'] = 0;
            if ($company) {
                $list[$k]['view_flg'] = CompanyViewLog::where('cid', $company->id)->where('mid', $v->mid)->count() ? 1 : 0;
            }
        }
        /*if(config('app.env') == 'production'){
            return $this->success(['count' => 0,'list' => [],'page' => 1]);
        }*/
        return $this->success(['count' => $count, 'list' => $list, 'page' => $page, 'show' => Cache::get('xcx_show'), 'total' => $total]);

    }

    /**
     * 已读接口
     * @param Request $request
     * @return CompanyController
     */
    public function checkRead(Request $request)
    {
        $token = $request->get('token', '');
        $company = Companys::where('token', $token)->first();
        $log = CompanyCheckLog::find($company->check_log_id);
        if ($log) {
            if ($log->is_read == 2) {
                return $this->fail(2000005);
            }
            $log->is_read = 2;
            if ($log->save()) {
                return $this->success();
            } else {
                return $this->fail();
            }
        } else {
            return $this->success();
        }
    }

    /**
     * 手机号是否存在
     * @param Request $request
     * @return CompanyController
     */
    public function isPhoneExist(Request $request)
    {
        $phone = $request->get('phone', '');
        $flg = Companys::where('phone', $phone)->count();
        if ($flg) {
            return $this->success(['flg' => true]);
        } else {
            return $this->success(['flg' => false]);
        }
    }

    /**
     * 取消审核
     * @param Request $request
     * @return CompanyController
     */
    public function cancelCheck(Request $request)
    {
        $token = $request->get('token', '');
        $company = Companys::where('token', $token)->where('status', 1)->first();
        if (!$company) {
            return $this->fail(2000005);
        }
        try {
            DB::beginTransaction();
            $company->status = 0;
            $company->submit_num - 1;
            $flg = CompanyCheckLog::where('id', $company->check_log_id)->delete();
            @$company->check_log_id = null;
            if ($company->save() && $flg) {
                DB::commit();
                return $this->success();
            } else {
                DB::rollback();
                return $this->fail();
            }
        } catch (\Exception $e) {
            DB::rollback();
            Log::info('取消审核失败:' . $e->getMessage());
            return $this->fail();
        }
    }

    /**
     * 提交资料
     * @param Request $request
     * @return CompanyController
     */
    public function submitCompany(Request $request)
    {
        $data = $request->all();
        $company = $request->company->where('token', $data['token'])->whereIn('status', [0, 2, 3])->first();
        if (!$company) {
            return $this->fail(2000001);
        }
        unset($data['company']);
        $data['status'] = 1;
        $data['submit_type'] = isset($data['submit_type']) ? ($data['submit_type'] == 1 || $data['submit_type'] == 2 ? $data['submit_type'] : 1) : 1;
        try {
            DB::beginTransaction();
            $model = new CompanyCheckLog();
            $model->cid = $company->id;
            $model->submit_time = date("Y-m-d H:i:s");
            $model->save();
            $data['submit_num'] = $company->submit_num + 1;
            $data['check_log_id'] = $model->id;
            $flg = Companys::where('id', $request->company->id)->update($data);
            if ($flg) {
                if ($data['submit_num'] == 1) {
                    Notice::addNotice(returnNoticeMsg(['company_name' => $company->company_name], 1004), 1, 1004);
                } else {
                    Notice::addNotice(returnNoticeMsg(['company_name' => $company->company_name], 2001), 2, 2001);
                }
                Event::addEvent($company->company_name . '提交入驻申请', $company->id, 2);
                //发送飞书通知
                if (config('app.env') == 'production') {
                    //获取运营部通知手机好
                    $phones = $this->getYunYingUserPhone();
                    //获取通知内容
                    $Feishu['company_name'] = $company->company_name;
                    $Feishu['time'] = date("Y年m月d日 H:i");
                    $this->FeiShuSendText($phones, returnFeiShuMsg($data['submit_type'], $Feishu));
                }
                DB::commit();
                return $this->success();
            } else {
                DB::rollback();
                return $this->fail();
            }
        } catch (\Exception $e) {
            DB::rollback();
            Log::info('提交审核失败:' . $e->getMessage());
            return $this->fail();
        }
    }
}
