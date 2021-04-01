<?php

namespace App\Http\Controllers\Admin;

use App\Jobs\SendWxNotice;
use App\Models\Backup;
use App\Models\Collect;
use App\Models\CompanyAdvier;
use App\Models\CompanyCheckLog;
use App\Models\Companys;
use App\Models\CompanyViewLog;
use App\Models\Event;
use App\Models\Files;
use App\Models\ImUser;
use App\Models\Interview;
use App\Models\Job;
use App\Models\MemberInfo;
use App\Models\Notice;
use App\Models\Official;
use App\Models\Region;
use App\Models\SignContract;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CompanyController extends Controller
{

    public function updateMemo(Request $request){
        Companys::where('id',$request->id)->update(['memo' => $request->memo]);
        return $this->success();
    }
    
    public function add(Request $request)
    {
        $user = !empty($request->user_id) ? $request->user_id : $request->user->id;
        $data = array_filter($request->only([
            'phone',        //手机号
//            'password',     //登录密码
            'company_name', //机构名称
            'business_flg', //营业执照flg 0无 1有
            'business_img', //营业执照
            'type',         //机构类型 1培训机构  2公立学校 3私立学校 4中介机构 5幼儿园 6其他
            'city',         //城市id
            'address',      //详细办公地址
            'talent',       //资质 0不具备 1具备
            'talent_img',   //资质证书图片
            'student_age',  //学生年龄
            'abroad_staff', //外籍员工数
            'needs_num',    //年度外教需求数
            'pay',          //月均薪资(税后) 1 15000以下 2 15000-20000 3 20000以上
            'school_img_1', //学区照片1
            'school_img_2', //学区照片2
            'contact',      //联系人
            'contact_phone',//联系电话
            'work_email',   //联系邮箱
            'logo',         //企业logo
            'memo',
            'school_en_info',
        ]));
        try {
            DB::beginTransaction();
            $data['status'] = 2;
//            if(@$data['password']){
            $data['password'] = md5(md5('1234567890'));
//            }
            $data['check_ok_time'] = date("Y-m-d H:i;s");
            //添加企业
            $company = Companys::create($data);
            Log::info(json_encode($company));
            //添加顾问
            $advier = CompanyAdvier::create(['cid' => $company->id, 'uid' => $user]);
            //添加IM
            $im_user = ImUser::create(['type' => 2, 'user_id' => $company->id]);
            Notice::addNotice(returnNoticeMsg(['user' => $request->user->name, 'company_name' => $company->company_name], 2010), 2, 2010);
            //记录日志
            Event::addEvent($request->user->name . ' 添加了用户', $company->id, 2);
            if ($company && $advier && $im_user) {
                $logo_url = config('app.url') . '/logo/company_defaut_logo.png';
                if (isset($data['logo'])) {
                    $logo_url = Files::where('id', $data['logo'])->pluck('path')->first();
                }
                $res = $this->createImOneAccount(['Identifier' => config('app.env') . '_' . $im_user->id, 'Nick' => $data['company_name'], 'FaceUrl' => $logo_url]);
                $res = json_decode($res, true);
                if ($res['ActionStatus'] == 'OK') {
                    //发送消息
                    $imUser = ImUser::where('user_id', $user)->where('type', 3)->first();
                    $sendMsgRes = $this->sendImMsg(
                        config('app.env') . '_' . $imUser->id,
                        config('app.env') . '_' . $company->id,
                        '您好，欢迎使用寰球阿帕斯，有问题可以随时咨询'
                    );
                    $sendMsgRes = json_decode($sendMsgRes, true);
                    if ($sendMsgRes['ActionStatus'] != 'OK') {
                        DB::rollback();
                        return $this->fail(100000, 'IM消息发送失败,错误码:' . $res['ErrorCode'] . ' 错误描述:' . $res['ErrorInfo']);
                    } else {
                        DB::commit();
                        return $this->success($company);
                    }
                } else {
                    DB::rollback();
                    return $this->fail(100000, 'IM消息发送失败,错误码:' . $res['ErrorCode'] . ' 错误描述:' . $res['ErrorInfo']);
                }
            } else {
                DB::rollBack();
                return $this->fail();
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->fail(100000, json_encode($e->getTrace()));
        }
    }

    public function edit(Request $request)
    {
        $id = $request->get('id', 0);
        if (!$id) return $this->fail(100001);
        $user = !empty($request->user_id) ? $request->user_id : $request->user->id;
        $data = array_filter($request->only([
            'phone',        //手机号
//            'password',     //登录密码
            'company_name', //机构名称
            'business_flg', //营业执照flg 0无 1有
            'business_img', //营业执照
            'type',         //机构类型 机构类型 1培训机构  2公立学校 3私立学校 4中介机构 5幼儿园 6其他
            'city',         //城市id
            'address',      //详细办公地址
            'talent',       //资质 0不具备 1具备
            'talent_img',   //资质证书图片
            'student_age',  //学生年龄字符串
            'abroad_staff', //外籍员工数
            'needs_num',    //年度外教需求书
            'pay',          //月均薪资(税后) 1 15000以下 2 15000-20000 3 20000以上
            'school_img_1', //学区照片1
            'school_img_2', //学区照片2
            'contact',      //联系人
            'contact_phone',//联系电话
            'work_email',   //联系邮箱
            'logo',         //企业logo
            'memo',
            'school_en_info',
        ]));
        try {
            DB::beginTransaction();
            $data['status'] = 2;
//            $data['password'] = $data['password'] ? md5(md5($data['password'])) : md5(md5('1234567890'));
            //修改企业
            $company = Companys::find($id);
            $companyFlg = $company->update($data);
            //修改顾问
            $advier = CompanyAdvier::where('cid', $id)->update(['uid' => $user]);
            //记录日志
            Event::addEvent($request->user->name . ' 修改了用户信息', $company->id, 2);
            Notice::addNotice(returnNoticeMsg(['user' => $request->user->name, 'company_name' => $company->company_name], 2004), 2, 2004);
            if ($companyFlg && $advier) {
                $im_user = ImUser::where('user_id', $id)->where('type', 2)->first();
                $logo_url = config('app.url') . '/logo/company_defaut_logo.png';
                if ($company->logo) {
                    $logo_url = Files::where('id', $company->logo)->pluck('path')->first();
                }
                $res = $this->createImOneAccount(['Identifier' => config('app.env') . '_' . $im_user->id, 'Nick' => $company->company_name, 'FaceUrl' => $logo_url]);
                $res = json_decode($res, true);
                if ($res['ActionStatus'] == 'OK') {
                    DB::commit();
                    return $this->success($company);
                } else {
                    DB::rollback();
                    return $this->fail(100000, 'IM消息发送失败,错误码:' . $res['ErrorCode'] . ' 错误描述:' . $res['ErrorInfo']);
                }
            } else {
                DB::rollBack();
                return $this->fail();
            }
        } catch (\Exception $e) {
            Log::info(json_encode($e->getMessage()));
            DB::rollBack();
            return $this->fail(100000, $e->getMessage());
        }
    }

    public function delete(Request $request)
    {
        $id = $request->get('id', 0);
        $user_id = $request->user->id;
        if (!$id) {
            return $this->fail(100001);
        }
        $company = Companys::find($id);
        if (!$company) {
            return $this->fail(100001);
        }
        DB::beginTransaction();
        try {
            //备份企业用户数据
            $add_flg = Backup::create(['json' => json_encode($company), 'type' => 1, 'user_id' => $user_id]);
            //删除企业表
            $flg1 = Companys::where('id', $id)->delete();
            //删除收藏表
            $flg2 = Collect::where('cid', $id)->delete();
            //删除企业顾问表
            $flg3 = CompanyAdvier::where('cid', $id)->delete();
            //删除企业审核表
            $flg4 = CompanyCheckLog::where('cid', $id)->delete();
            //查出企业查看表
            $flg5 = CompanyViewLog::where('cid', $id)->delete();
            //删除面试记录表
            $flg6 = Interview::where('cid', $id)->delete();
            //删除IM User
            $flg7 = ImUser::where('type', 2)->where('user_id', $id)->delete();
            //删除职位表
            $flg8 = Job::where('cid', $id)->delete();
            //删除签约表
//        $flg9 = SignContract::where('cid',$id)->delete();

            //记录日志
            DB::commit();
            return $this->success();
        } catch (\Exception $e) {
            DB::rollback();
            return $this->fail(100000, $e->getMessage());
        }
    }


    /**
     * 添加企业账号
     * @param Request $request
     * @return CompanyController
     */
//    public function addCompany(Request $request){
//        $phone      = $request->get('phone');
//        $password   = $request->get('password');
//        $flg = Companys::where('phone',$phone)->count();
//        if($flg){
//            return $this->fail(100003);
//        }
//        DB::beginTransaction();
//        $model = new Companys();
//        $model->phone           = $phone;
//        $model->password        = md5(md5($password));
//        $model->register_ip     = $request->getClientIp();
//        $model->register_time   = date('Y-m-d H:i:s');
//        if($model->save()){
//            $model1 = new ImUser();
//            $model1->type = 2;
//            $model1->user_id = $model->id;
//            if($model1->save()){
//                DB::commit();
//                return $this->success();
//            }else{
//                DB::rollback();
//                Log::info('企业用户增加失败了1');
//                return $this->fail();
//            }
//        }else{
//            DB::rollback();
//            Log::info('企业用户增加失败了');
//            return $this->fail();
//        }
//    }

    //配置顾问
    public function adviser(Request $request)
    {
        $id = $request->get('id', 0);
        $uid = $request->get('uid', '');
        if (!$id || !$uid) {
            return $this->fail(100001);
        }
        $user = User::find($uid);
        $company = Companys::find($id);
        try {
            DB::beginTransaction();
            //删除旧配置
            $flg = CompanyAdvier::where('cid', $id)->delete();
            //添加新配置
            Event::addEvent($request->user->name . ' 变更顾问为:' . $user->name, $company->id, 2);
            Notice::addNotice(returnNoticeMsg(['user' => $request->user->name, 'company_name' => $company->company_name, 'adviser_name' => $user->name], 2008), 2, 2008);
            $model = new CompanyAdvier();
            $model->cid = $id;
            $model->uid = $user->id;
            if (!$model->save()) {
                DB::rollback();
                return $this->fail();
            }
            $company->gw_flg = 2;
            if ($flg !== false && $company->save()) {
                DB::commit();
                //发送消息
                $imUser = ImUser::where('user_id', $user->id)->where('type', 3)->first();
                $sendMsgRes = $this->sendImMsg(
                    config('app.env') . '_' . $imUser->id,
                    config('app.env') . '_' . $company->id,
                    '您好，我是您的专属顾问，您有任何问题都可以咨询我'
                );
                $sendMsgRes = json_decode($sendMsgRes, true);
                if ($sendMsgRes['ActionStatus'] != 'OK') {
                    return $this->fail(100000, 'IM消息发送失败,错误码:' . $sendMsgRes['ErrorCode'] . ' 错误描述:' . $sendMsgRes['ErrorInfo']);
                }
                return $this->success();
            } else {
                DB::rollback();
                return $this->fail();
            }

        } catch (\Exception $e) {
            Log::info('添加顾问出错了：' . $e->getMessage());
            DB::rollback();
            return $this->fail();
        }
    }

    //顾问列表
    public function adviserList()
    {
        $list = User::where('status', 0)->get(['id', 'name']);
        return $this->success($list);
    }

    /**
     * 企业管理详情
     * @param Request $request
     * @return CompanyController
     */
    public function desc(Request $request)
    {
        $id = $request->get('id', 0);
        if (!$id) {
            return $this->fail(2000005);
        }
        $data = Companys::find($id);
        unset($data['password']);
        unset($data['token']);
        unset($data['token_expire_time']);
        if ($data->city) {
            $city = Region::find($data->city);
        }
        $data['city_name'] = $data->city ? $city : '';
        $log = CompanyCheckLog::find($data->check_log_id);
        if ($log) {
            $data['apply_time'] = $log->submit_time;
        } else {
            $data['apply_time'] = '';
        }
        if ($data->logo) {
            $data['logo_path'] = Files::find($data->logo);
        } else {
            $data['logo_path'] = null;
        }
        if ($data->invite_code) {
            $data->invite_data = Companys::where('code', $data->invite_code)->first(['company_name', 'phone']);
        } else {
            $data->invite_data = null;
        }

        if ($data->gw_flg) {
            $ad_list = CompanyAdvier::where('cid', $data->id)->get(['uid']);
            $userList = User::whereIn('id', $ad_list)->get(['id', 'name']);
            $data['adviser'] = $userList;
        } else {
            $data['adviser'] = [];
        }

        if ($data->talent == 1) {
            $data->talent_img_path = Files::whereIn('id', explode(',', $data->talent_img))->get();
        } else {
            $data->talent_img_path = [];
        }
        if ($data->school_img_1) {
            $data->school_img_1_path = Files::where('id', $data->school_img_1)->first();
        } else {
            $data->school_img_1_path = null;
        }
        if ($data->school_img_2) {
            $data->school_img_2_path = Files::where('id', $data->school_img_2)->first();
        } else {
            $data->school_img_2_path = null;
        }
        $data->business_img_path = Files::whereIn('id', explode(',', $data->business_img))->get();
        $job_list = Job::where('cid', $id)->orderBy('id', 'desc')->get(['id', 'name']);
        $data['job_list'] = $job_list;
        return $this->success($data);
    }


    /**
     * 企业管理列表
     * @param Request $request
     * @return CompanyController
     */
    public function list(Request $request)
    {
        $company_name = $request->get('company_name', '');
        $phone = $request->get('phone', '');
        $type = $request->get('type', '');
        $city = $request->get('city', '');
        $gw_flg = $request->get('gw_flg', 0);
        $page = $request->get('page', 1);
        $pageSize = $request->get('pageSize', config('admin.pageSize'));
        if ($page < 1) $page = 1;
        $list = Companys::where('status', 2);
        if ($company_name) {
            $list = $list->where('company_name', 'like', '%' . $company_name . '%');
        }
        if ($phone){
            $list = $list->where('phone', 'like', '%' . $phone . '%');
        }

        if ($gw_flg) {
            $list = $list->where('gw_flg', $gw_flg);
        }
        if ($type) {
            $list = $list->where('type', $type);
        }
        if ($city) {
            /*$cityData = Region::find($city);
            if($cityData->level == 1){
                //查询下级
                $cityList = Region::where('pid',$cityData->id)->orWhere('id',$cityData->id)->get(['id']);
                $cityList = Region::whereIn('pid',$cityList)->get(['id']);
            }elseif($cityData->level == 2){
                $cityList = Region::where('pid',$cityData->id)->orWhere('id',$cityData->id)->get(['id']);
            }else{
                $cityList = [$city];
            }*/
            $region_arr = Region::where('pid',$city)->orWhere('id',$city)->pluck('id');
            $list = $list->whereIn('city', $region_arr);
        }
        $count = ceil($list->count() / $pageSize);
        $list = $list
            ->orderBy('check_ok_time', 'desc')
            ->offset(($page - 1) * $pageSize)
            ->limit($pageSize)
            ->get([
                'id',
                'phone',
                'company_name',
                'business_img',
                'type',
                'city',
                'address',
                'talent',
                'talent_img',
                'student_age',
                'abroad_staff',
                'needs_num',
                'pay',
                'contact',
                'contact_phone',
                'work_email',
                'status',
                'submit_num',
                'check_log_id',
                'gw_flg',
                'invite_code',
                'vip_actions_id',
                'created_at',
                'memo',
            ]);
        foreach ($list as $k => $v) {
            if ($v->city) {
                $city = Region::find($v->city);
            }
            if ($v->invite_code) {
                $v->invite_data = Companys::where('code', $v->invite_code)->first(['company_name', 'phone']);
            } else {
                $v->invite_data = null;
            }
            $v['city_name'] = $v->city ? $city : '';

            $log = CompanyCheckLog::find($v->check_log_id);
            if ($log) {
                $v['apply_time'] = $log->submit_time;
            } else {
                $v['apply_time'] = $v->created_at->format('Y-m-d H:i:s');
            }
            if ($v->gw_flg) {
                $ad_list = CompanyAdvier::where('cid', $v->id)->get(['uid']);
                $userList = User::whereIn('id', $ad_list)->get(['id', 'name']);
                $v['adviser'] = $userList;
            } else {
                $v['adviser'] = [];
            }
        }
        return $this->success(['count' => $count, 'list' => $list, 'page' => $page]);
    }

    /**
     * 管理员更新企业信息
     * @param Request $request
     * @return CompanyController
     */
    public function updateCompany(Request $request)
    {
        $data = $request->all();
        $id = $data['id'];
        unset($data['id']);
        unset($data['token']);
        unset($data['user']);
        if (count($data) < 1) {
            return $this->fail(100001);
        }
        $flg = Companys::where('status', 2)->where('id', $id)->update($data);
        if ($flg !== false) {
            return $this->success();
        } else {
            return $this->fail();
        }
    }

    ########################分隔线##################################

    /**
     * 审核日志
     * @param Request $request
     * @return CompanyController
     */
    public function checkLog(Request $request)
    {
        $id = $request->get('id', 0);
        if (!$id) {
            return $this->fail(100001);
        }
        $data = Companys::where('id', $id)->first();
        if (!$data) {
            return $this->fail(2000005);
        }
        $list = CompanyCheckLog::from('company_check_log as a')
            ->leftjoin('companys as b', 'a.cid', '=', 'b.id')
            ->leftjoin('user as d', 'a.uid', '=', 'd.id')
            ->where('a.cid', $data->id)
            ->orderBy('a.id', 'desc')
            ->get(['a.*', 'b.company_name', 'd.name as admin_name']);
        return $this->success($list);
    }

    /**
     * 审核接口
     * @param Request $request
     * @return CompanyController
     */
    public function check(Request $request)
    {
        $type = $request->get('type', 0);//0审核通过 1审核驳回
        $info = $request->get('info', '');//审核详情
        $token = $request->get('token', '');
        $id = $request->get('id', '');
        if (!$id) {
            return $this->fail(100001);
        }
        $user = User::where('token', $token)->first();
        try {
            DB::beginTransaction();
            $company = Companys::find($id);
            if (!$company) {
                return $this->fail(2000005);
            }
            $company->status = $type == 0 ? 2 : 3;
            if ($company->status === 2) {
                $company->check_ok_time = date("Y-m-d H:i;s");
                Event::addEvent($request->user->name . ' 通过入驻申请', $company->id, 2);
                Notice::addNotice(returnNoticeMsg(['user' => $request->user->name, 'company_name' => $company->company_name], 1006), 1, 1006);
                $company->gw_flg = 2;
                $model1 = new CompanyAdvier();
                $model1->cid = $id;
                $model1->uid = $user->id;
                $save1 = $model1->save();
            } else {
                Event::addEvent($request->user->name . ' 驳回入驻申请', $company->id, 2);
                Notice::addNotice(returnNoticeMsg(['user' => $request->user->name, 'company_name' => $company->company_name], 1008), 1, 1008);
                $save1 = 1;
            }
            $model = CompanyCheckLog::find($company->check_log_id);
            $model->check = $type == 0 ? 1 : 2;
            $model->info = $info;
            $model->is_read = 1;
            $model->uid = $user->id;
            if ($model->save() && $company->save() && $save1) {
                if ($type == 0) {
                    if ($company->unionid && config('app.env') == 'production') {
                        $officials = Official::where('unionid', $company->unionid)->where('status', 1)->first();
                        if ($officials) {
                            //发送审核通过微信通知
                            $wxNoticeData = [
                                'openid' => $officials->openid,
                                'type' => 3,
                                'title' => '亲爱的，您提交的资料现已审核成功',
                                'memo' => '您可在小程序或电脑端预约外教面试。',
                                'key' => ['keyword1' => $company->company_name, 'keyword2' => date("Y年m月d日 H:i")],
                            ];
                            $this->dispatch(new SendWxNotice($wxNoticeData));
                        }
                    }
                    $model2 = ImUser::where('user_id', $company->id)->where('type', 2)->first();
                    if ($company->logo) {
                        $logo_url = Files::where('id', $company->logo)->pluck('path')->first();
                    } else {
                        $logo_url = config('app.url') . '/logo/company_defaut_logo.png';
                    }
                    $res = $this->createImOneAccount(['Identifier' => config('app.env') . '_' . $model2->id, 'Nick' => $company->company_name, 'FaceUrl' => $logo_url]);
                    $res = json_decode($res, true);
                    if ($res['ActionStatus'] == 'OK') {
                        //发送消息
                        $imUser1 = ImUser::where('user_id', $user->id)->where('type', 3)->first();
                        $sendMsgRes = $this->sendImMsg(
                            config('app.env') . '_' . $imUser1->id,
                            config('app.env') . '_' . $model2->id,
                            '您好，我是您的专属顾问，您有任何问题都可以咨询我'
                        );
                        $sendMsgRes = json_decode($sendMsgRes, true);
                        if ($sendMsgRes['ActionStatus'] != 'OK') {
                            DB::rollback();
                            Log::info('IM消息发送失败,错误码:' . json_encode($sendMsgRes));
                            return $this->fail();
                        } else {
                            DB::commit();
                            return $this->success();
                        }
                    } else {
                        DB::rollback();
                        Log::info('IM注册失败了,错误码:' . json_encode($res));
                        return $this->fail();
                    }
                } else {
                    //发送审核失败微信通知
                    if ($company->unionid && config('app.env') == 'production') {
                        $officials = Official::where('unionid', $company->unionid)->where('status', 1)->first();
                        if ($officials) {
                            //发送审核通过微信通知
                            $wxNoticeData = [
                                'openid' => $officials->openid,
                                'type' => 8,
                                'title' => '亲爱的，您提交的资料现未能通过审核，请重新填写资料提交审核',
                                'memo' => '原因请在寰球阿帕斯小程序，或电脑端进行查看。',
                                'key' => [
                                    'keyword1' => '未通过',
                                    'keyword2' => $info,
                                    'keyword3' => date("Y年m月d日 H:i")
                                ],
                            ];
                            $this->dispatch(new SendWxNotice($wxNoticeData));
                        }
                    }

                    DB::commit();
                    return $this->success();
                }
            } else {
                DB::rollBack();
                return $this->fail();
            }
        } catch (\Exception $e) {
            Log::info('审核出错了：' . $e->getMessage() . $e->getLine());
            DB::rollBack();
            return $this->fail();
        }


    }

    /**
     * 企业入驻审核详情
     * @param Request $request
     * @return CompanyController
     */
    public function checkDesc(Request $request)
    {
        $id = $request->get('id', 0);
        if (!$id) {
            return $this->fail(2000005);
        }
        $data = Companys::find($id);
        unset($data['password']);
        unset($data['token']);
        unset($data['token_expire_time']);
        if ($data->city) {
            $city = Region::find($data->city);
        }
        $data['city_name'] = $data->city ? $city : '';
        if ($data->logo) {
            $data['logo_path'] = Files::find($data->logo);
        } else {
            $data['logo_path'] = null;
        }
        $log = CompanyCheckLog::find($data->check_log_id);
        if ($log) {
            $data['apply_time'] = $log->submit_time;
        } else {
            $data['apply_time'] = '';
        }
        if ($data->invite_code) {
            $data->invite_data = Companys::where('code', $data->invite_code)->first(['company_name', 'phone']);
        } else {
            $data->invite_data = null;
        }
        if ($data->talent == 1) {
            $data->talent_img_path = Files::whereIn('id', explode(',', $data->talent_img))->get();
        } else {
            $data->talent_img_path = [];
        }
        if ($data->school_img_1) {
            $data->school_img_1_path = Files::where('id', $data->school_img_1)->first();
        } else {
            $data->school_img_1_path = null;
        }
        if ($data->school_img_2) {
            $data->school_img_2_path = Files::where('id', $data->school_img_2)->first();
        } else {
            $data->school_img_2_path = null;
        }
        $data->business_img_path = Files::whereIn('id', explode(',', $data->business_img))->get();
        return $this->success($data);
    }


    /**
     * 入驻审核列表
     * @param Request $request
     * @return CompanyController
     */
    public function checkList(Request $request)
    {
        $company_name = $request->get('company_name', '');
        $status = $request->get('status', 2);
        $page = $request->get('page', 1);
        $pageSize = $request->get('pageSize', config('admin.pageSize'));
        if ($page < 1) $page = 1;

        $list = Companys::from('companys as a')
            ->leftjoin('company_check_log as b', 'a.check_log_id', '=', 'b.id')
            ->where('a.id', '>', 0);
        if ($company_name) {
            $list = $list->where('a.company_name', 'like', '%' . $company_name . '%');
        }
        if ($status) {
            $list = $list->where('a.status', $status - 1);
        }

        $count = ceil($list->count() / $pageSize);
        $list = $list
            ->orderBy('b.submit_time', 'desc')
            ->orderBy('a.id', 'desc')
            ->offset(($page - 1) * $pageSize)
            ->limit($pageSize)
            ->get([
                'a.id',
                'a.phone',
                'a.company_name',
                'a.business_img',
                'a.type',
                'a.city',
                'a.address',
                'a.talent',
                'a.talent_img',
                'a.student_age',
                'a.abroad_staff',
                'a.needs_num',
                'a.pay',
                'a.contact',
                'a.contact_phone',
                'a.work_email',
                'a.status',
                'a.submit_num',
                'a.check_log_id',
                'a.invite_code',
                'a.created_at',
                'a.memo',
                'b.submit_time as apply_time']);
        foreach ($list as $k => $v) {
            if ($v->city) {
                $city = Region::find($v->city);
            }
            if ($v->invite_code) {
                $v->invite_data = Companys::where('code', $v->invite_code)->first(['company_name', 'phone']);
            } else {
                $v->invite_data = null;
            }
            $v['city_name'] = $v->city ? $city : '';

            /*$log = CompanyCheckLog::find($v->check_log_id);
            if($log){
                $v['apply_time'] = $log->submit_time;
            }else{
                $v['apply_time'] = '';
            }*/
            $job = Job::where('cid', $v->id)->orderBy('id', 'desc')->first();
            if ($job) {
                $v['job'] = $job->id;
            } else {
                $v['job'] = null;
            }
        }


        return $this->success(['count' => $count, 'list' => $list, 'page' => $page]);
    }
}
