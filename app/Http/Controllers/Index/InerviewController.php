<?php

namespace App\Http\Controllers\Index;

use App\Jobs\SendEmail;
use App\Jobs\SendWxNotice;
use App\Models\Collect;
use App\Models\CompanyAdvier;
use App\Models\Companys;
use App\Models\Country;
use App\Models\Evaluates;
use App\Models\Files;
use App\Models\ImUser;
use App\Models\Interview;
use App\Models\InterviewLogs;
use App\Models\Invite;
use App\Models\Job;
use App\Models\Member;
use App\Models\MemberAdviser;
use App\Models\MemberInfo;
use App\Models\MemberInfoChecked;
use App\Models\Notice;
use App\Models\Official;
use App\Models\Region;
use App\Models\Rooms;
use App\Models\User;
use App\Models\Vip;
use App\Models\VipAction;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use TencentCloud\Common\Credential;
use TencentCloud\Common\Exception\TencentCloudSDKException;
use TencentCloud\Common\Profile\ClientProfile;
use TencentCloud\Common\Profile\HttpProfile;
use TencentCloud\Trtc\V20190722\Models\DismissRoomRequest;
use TencentCloud\Trtc\V20190722\TrtcClient;

class InerviewController extends Controller
{

    public function closeRoome(Request $request){
        $room_id = $request->get('room_id','');
        if(!$room_id){
            return $this->fail(100001);
        }
        try {
            $cred = new Credential(config('videos.SECRET_ID'), config('videos.SECRET_KEY'));
            $httpProfile = new HttpProfile();
            $httpProfile->setEndpoint("trtc.tencentcloudapi.com");

            $clientProfile = new ClientProfile();
            $clientProfile->setHttpProfile($httpProfile);
            $client = new TrtcClient($cred, "ap-beijing", $clientProfile);
            $req = new DismissRoomRequest();
            $params = "{\"SdkAppId\":".config('videos.SDK_APP_ID').",\"RoomId\":".$room_id."}";
            $req->fromJsonString($params);
            $resp = $client->DismissRoom($req);
//            print_r($resp->toJsonString());
            $data = Rooms::find($room_id);
            $model = Interview::find($data->inter_id);
            $model->status = 6;
            DB::beginTransaction();
            if($model->save()){
                //候选
                $collect = new Collect();
                $collect->cid = $model->cid;
                $collect->mid = $model->mid;
                $collect->type= 2;
                if($collect->save()){
                    $teach = MemberInfo::where('mid',$model->mid)->first();
                    $teach_account = Member::find($model->mid);
                    $company = Companys::find($model->cid);
                    $emailData['teach_name'] = $teach->name . ' ' .$teach->last_name;
                    $emailData['company_name'] = $company->company_name;
                    $emailData['time'] = date("H:i Y/m/d",strtotime($model->inte_time));
                    $email = $teach_account->email;
                    $type_email_temp = 'email.mianshi_daiding';
                    Notice::addNotice(returnNoticeMsg(['res'=>'备选','teach_name' => $teach->name . ' ' .$teach->last_name,'company_name' => $company->company_name,'time' =>$emailData['time']],3010),3,3010);
                    $modelLog = new InterviewLogs();
                    $modelLog->vid = $data->inter_id;
                    $msgData['company_name'] = $company->company_name;
                    $msgData['teach_name'] = $teach->name . ' ' .$teach->last_name;
                    $msgData['time'] = $model->inte_time;
                    $msgData['res'] = '备选';
                    $msg = interViewLogMsg($msgData,6);
                    $modelLog->info    = $msg[0];
                    $modelLog->info1    = $msg[1];
                    $modelLog->save();

                    Mail::send($type_email_temp,['emailData' => $emailData],function($message)use($email){
                        $message ->to($email)->subject('寰球阿帕斯');
                    });
                    DB::commit();
                    return $this->success($resp);
                }else{
                    DB::rollBack();
                    Log::info('保存候选失败');
                    return $this->fail(2000207);
                }
            }else{
                DB::rollBack();
                Log::info('修改状态失败');
                return $this->fail(2000207);
            }
//
        }catch(TencentCloudSDKException $e) {
            DB::rollBack();
            Log::info('解散房间失败:'.$e->getMessage());
            return $this->fail(2000207);
        }
    }

    #外教同意面试 / 外教拒绝面试
    public function joinInterview(Request $request)
    {
        $id = $request->get('id', '');
        $type = $request->get('type', 1);//1参加 2不参加
        if (!$id) {
            return $this->fail(2000001);
        }
        try {
            DB::beginTransaction();
            $model = Interview::where('id', $id)->where('mid', $request->member->id)->first();
            if (!$model) {
                return $this->fail(2000201);
            }
            $teach = MemberInfo::where('mid', $model->mid)->first();
            $company = Companys::find($model->cid);
            $company_adviser = CompanyAdvier::where('cid', $company->id)->first();
            $user = User::find($company_adviser->uid);
            $emailData['time'] = date("H:i Y/m/d", strtotime($model->inte_time));
            $modelLog = new InterviewLogs();
            $modelLog->vid = $id;
            $modelLog->vid = $model->id;
            $msgData['company_name'] = $company->company_name;
            $msgData['teach_name'] = $teach->name . ' ' . $teach->last_name;
            $msgData['time'] = $model->inte_time;
            $msgData['adviser_name'] = $user->name;
            if ($model->status > 1) {
                return $this->fail(2000001);
            }
            switch ($type) {
                case 1:
                    $model->status = 1;
                    $msg = interViewLogMsg($msgData, 2);
                    break;
                case 2:
                    $model->status = 9;
                    $request->info && $model->info = $request->info;
                    $msg = interViewLogMsg($msgData, 3);
                    break;
            }
            $modelLog->info = $msg[0];
            $modelLog->info1 = $msg[1];
            if ($modelLog->save() && $model->save()) {
                switch ($type) {
                    case 1://确认面试
                        Notice::addNotice(returnNoticeMsg(['adviser_name' => $user->name, 'teach_name' => $teach->name . ' ' . $teach->last_name, 'company_name' => $company->company_name, 'time' => $emailData['time']], 3006), 3, 3006);
                        break;
                    case 2://取消面试
                        Notice::addNotice(returnNoticeMsg(['adviser_name' => $user->name, 'teach_name' => $teach->name . ' ' . $teach->last_name, 'company_name' => $company->company_name, 'time' => $emailData['time']], 3007), 3, 3007);
                        break;
                }
                //发送审核失败微信通知
                if (config('app.env') == 'production') {
                    //发送飞书通知
                    $phones = $this->getYunYingUserPhone();
                    //获取通知内容
                    $Feishu['company_name'] = $company->company_name;
                    $Feishu['teach_name'] = $teach->name . ' ' . $teach->last_name;
                    $Feishu['phone'] = $company->phone;
                    $this->FeiShuSendText($phones, returnFeiShuMsg($type == 1 ? 10 : 11, $Feishu));

                    //发送企业公众号通知
                    if ($company->unionid) {
                        $officials = Official::where('unionid', $company->unionid)->where('status', 1)->first();
                        if ($officials) {
                            if ($type == 1) {
                                $job = Job::find($model->jid);
                                //发送微信通知
                                $wxNoticeData = [
                                    'openid' => $officials->openid,
                                    'type' => 7,
                                    'title' => '您预约与外教的面试已成功，请安排好时间准时参加面试哦。',
                                    'memo' => '请在电脑端准时参加面试。',
                                    'key' => [
                                        'keyword1' => date("Y年m月d日 H:i", strtotime($model->inte_time)),
                                        'keyword2' => $teach->name . ' ' . $teach->last_name,
                                        'keyword3' => '17001213999',
                                        'keyword4' => $job->name,
                                    ],
                                ];
                                $this->dispatch(new SendWxNotice($wxNoticeData));
                            } elseif ($type == 2) {
                                $job = Job::find($model->jid);
                                //发送微信通知
                                $wxNoticeData = [
                                    'openid' => $officials->openid,
                                    'type' => 6,
                                    'title' => '您预约的面试已被拒绝。',
                                    'memo' => '有疑问请联系您的顾问。',
                                    'key' => [
                                        'keyword1' => $company->company_name,
                                        'keyword2' => $job->name,
                                        'keyword3' => date("Y年m月d日 H:i", strtotime($model->inte_time)),
                                    ],
                                ];
                                $this->dispatch(new SendWxNotice($wxNoticeData));
                            }
                        }
                    }

                }
                DB::commit();
                return $this->success();
            } else {
                DB::rollback();
                return $this->fail();
            }
        } catch (\Exception $e) {
            Log::info($e->getMessage());
            DB::rollback();
            return $this->fail();
        }
    }

    //取消面试/修改面试时间
    public function teachUpdateInterview(Request $request)
    {
        $id = $request->get('id', '');
        $type = $request->get('type', 1);//1更新时间 2取消面试
        //面试记录
        $inter = Interview::where('id', $id)->first();
        $adviser = MemberAdviser::where('mid', $inter->mid)->first();
        if ($adviser) {
            $user = User::find($adviser->uid);
        } else {
            $user = User::find(14);
        }
        $teach = MemberInfo::where('mid', $inter->mid)->first();
        $company = Companys::find($inter->cid);

        $log_model = new InterviewLogs();
        $msgData['company_name'] = $company->company_name;
        $msgData['teach_name'] = $teach->name . ' ' . $teach->last_name;
        $msgData['time'] = $inter->inte_time;
        $msg = interViewLogMsg($msgData, $type == 1 ? 8 : 9);
        $log_model->vid = $inter->id;
        $log_model->info = $msg[0];
        $log_model->info1 = $msg[1];
        $log_model->save();
        //发送消息顾
        $GW_ImUser = ImUser::where('type', 3)->where('user_id', $user->id)->first();
        $TEACH_ImUser = ImUser::where('type', 1)->where('user_id', $inter->mid)->first();
        $send_text = $type == 1 ? 'Hello, I need to change the interview time' : 'Hello, I need to cancel the interview';
        $sendMsgRes = $this->sendImMsg(
            config('app.env') . '_' . $TEACH_ImUser->id,
            config('app.env') . '_' . $GW_ImUser->id,
            $send_text
        );
        $sendMsgRes = json_decode($sendMsgRes, true);
        if ($sendMsgRes['ActionStatus'] != 'OK') {
            Log::info('IM消息发送失败,错误码:' . $sendMsgRes['ErrorCode'] . ' 错误描述:' . $sendMsgRes['ErrorInfo']);
        }
        if ($type == 1) {
            Notice::addNotice(returnNoticeMsg(['company_name' => $company->company_name, 'teach_name' => $teach->name . ' ' . $teach->last_name, 'time' => date("Y年m月d日 H:i", strtotime($inter->inte_time))], 3004), 2, 3004);
        } else {
            Notice::addNotice(returnNoticeMsg(['company_name' => $company->company_name, 'teach_name' => $teach->name . ' ' . $teach->last_name, 'time' => date("Y年m月d日 H:i", strtotime($inter->inte_time))], 3005), 2, 3005);
        }
        if (config('app.env') == 'production') {
            //获取通知内容
            $Feishu['company_name'] = $company->company_name;
            $Feishu['time'] = date("Y年m月d日 H:i", strtotime($inter->inte_time));
            $Feishu['teach_name'] = $teach->name . ' ' . $teach->last_name;
            $this->FeiShuSendText([$user->phone], returnFeiShuMsg($type == 1 ? 10 : 11, $Feishu));

        }
        return $this->success();
    }

    /**
     * 面试结果 企业端
     *
     * @param Requeste $requeste
     * @return InerviewController
     */
    public function resultInter(Request $request)
    {
        $inter_id = $request->get('inter_id', 0);
        $type = $request->get('type', 0);//1通过 2未通过 3候选

        $all = $request->get('all', 0);//总体评分
        $qualities = $request->get('qualities', 0);//形象气质
        $skill = $request->get('skill', 0);//专业技能评分
        $info = $request->get('info', 0);//沟通表达评分
        $memo = $request->get('memo', '');//描述

        if (!$inter_id || !$type) {
            return $this->fail(100001);
        }
        $interView = Interview::find($inter_id);
        if (Evaluates::where('iid', $inter_id)->count() > 0) {
            return $this->fail(2000204);
        }
        try {
            DB::beginTransaction();
            $flg = Interview::where('id', $inter_id)->update(['status' => $type == 1 ? 3 : ($type == 2 ? 5 : 6)]);

            $model = new Evaluates();
            $model->iid = $inter_id;
            $model->cid = $interView->mid;
            $model->mid = $interView->mid;
            $model->all = $all;
            $model->qualities = $qualities;
            $model->skill = $skill;
            $model->info = $info;
            $model->memo = $memo;
            if ($flg !== false && $model->save()) {
                if ($type == 1 || $type == 2) {
                    //删除候选人
                    Collect::where("cid", $interView->cid)->where('mid', $interView->mid)->delete();
                }

                $type_email_temp = '';
                $teach = MemberInfo::where('mid', $interView->mid)->first();
                $teach_account = Member::find($interView->mid);
                $company = Companys::find($interView->cid);
                $emailData['teach_name'] = $teach->name . ' ' . $teach->last_name;
                $emailData['company_name'] = $company->company_name;
                $emailData['time'] = date("H:i Y/m/d", strtotime($interView->inte_time));
                $email = $teach_account->email;

                $log_model = new InterviewLogs();
                $msgData['company_name'] = $request->company->company_name;
                $msgData['teach_name'] = $teach->name . ' ' . $teach->last_name;
                $msgData['time'] = $interView->inte_time;
                $msg = interViewLogMsg($msgData, 6);
                $log_model->vid = $interView->id;
                $log_model->info = $msg[0];
                $log_model->info1 = $msg[1];

                switch ($type) {
                    case 1:// 1通过
                        $type_email_temp = 'email.mianshi_tongguo';
                        $msgData['res'] = '待签约';
                        Notice::addNotice(returnNoticeMsg(['res' => '待签约', 'teach_name' => $msgData['teach_name'], 'company_name' => $msgData['company_name'], 'time' => $emailData['time']], 3010), 3, 3010);
                        break;
                    case 2:// 2未通过
                        $type_email_temp = 'email.mianshi_weitongguo';
                        $msgData['res'] = '未通过';
                        Notice::addNotice(returnNoticeMsg(['res' => '未通过', 'teach_name' => $msgData['teach_name'], 'company_name' => $msgData['company_name'], 'time' => $emailData['time']], 3010), 3, 3010);
                        break;
                    case 3:// 3候选
//                        $type_email_temp = 'email.mianshi_daiding';
                        break;
                }
                $log_model->save();
                if ($type != 3) {
                    Mail::send($type_email_temp, ['emailData' => $emailData], function ($message) use ($email) {
                        $message->to($email)->subject('寰球阿帕斯');
                    });
                }
                DB::commit();
                return $this->success();
            } else {
                DB::rollback();
                return $this->fail();
            }
        } catch (\Exception $e) {
            DB::rollback();
            Log::info('评价失败：' . $e->getMessage());
            return $this->fail();
        }

    }

    /**
     * 教师端 面试邀请 只展示待面试记录
     *
     * @param Request $request
     * @return InerviewController
     */
    public function teachInterList(Request $request)
    {
        $page = $request->get('page', 1);
        $pageSize = $request->get('pageSize', 20);

        $member = Member::where('token', $request->token)->first();

        $list = Interview::from('interview as a')
            ->leftjoin('members_info as b', 'a.mid', '=', 'b.mid')
//            ->leftjoin('members as c','a.mid','=','c.id')
            ->leftjoin('companys as d', 'a.cid', '=', 'd.id')
            ->leftjoin('job as e', 'a.jid', '=', 'e.id')
            ->where('a.mid', $member->id)
            ->whereIn('a.status', [0, 1]);
        $count = ceil($list->count() / $pageSize);
        $list = $list->orderBy('a.id', 'desc')
            ->offset(($page - 1) * $pageSize)
            ->limit($pageSize)
            ->get(['a.*', 'd.company_name',
                'd.logo',
                'e.work_type',
                'e.sex',
                'e.name as job_name',
                'e.student_age',
                'e.pay',
                'e.money_type',
                'e.pay_type',
                'e.job_city',
                'e.type',
                'e.job_type',
                'e.language',
                'e.job_week_day',
                'e.job_day_time',
                'e.edu_type',
                'e.cert',
                'e.job_year',
                'e.job_info',
                'e.benefits',
                'b.id as info_mid']);
        foreach ($list as $k => $v) {
            $v->logo_path = Files::where('id', $v->logo)->first();
            if ($v->job_city) {
                $v->job_area_data = Region::find($v->job_city);
                $v->job_city_data = Region::find($v->job_area_data->pid);
                $v->job_province_data = Region::find($v->job_city_data->pid);
            } else {
                $v->job_area_data = [];
                $v->job_city_data = [];
                $v->job_province_data = [];
            }
        }
        return $this->success(['count' => $count, 'list' => $list, 'page' => $page]);
    }

    /**
     * 面试邀请详情
     *
     * @param Request $request
     * @return InerviewController
     */
    public function teachInterDesc(Request $request)
    {
        $member = Member::where('token', $request->token)->first();
        $id = $request->get('id', '');
        if (!$id) {
            return $this->fail(2000001);
        }
        $data = Interview::from('interview as a')
            ->leftjoin('members_info as b', 'a.mid', '=', 'b.mid')
            ->leftjoin('members as c','a.mid','=','c.id')
            ->leftjoin('companys as d', 'a.cid', '=', 'd.id')
            ->leftjoin('job as e', 'a.jid', '=', 'e.id')
            ->where('a.id', $request->id)
            ->whereIn('a.status', [0,1])
            ->first(['a.*',
                'd.company_name',
                'd.logo',
                'e.work_type',
                'e.sex',
                'e.name as job_name',
                'e.student_age',
                'e.pay',
                'e.money_type',
                'e.pay_type',
                'e.job_city',
                'e.type',
                'e.job_type',
                'e.language',
                'e.job_week_day',
                'e.job_day_time',
                'e.edu_type',
                'e.cert',
                'e.job_year',
                'e.job_info',
                'e.benefits']);
        if (!$data) {
            return $this->fail(2000201);
        }
        $data->logo_path = Files::where('id', $data->logo)->first();
        if ($data->job_city) {
            $data->job_area_data = Region::find($data->job_city);
            $data->job_city_data = Region::find($data->job_area_data->pid);
            $data->job_province_data = Region::find($data->job_city_data->pid);
        } else {
            $data->job_area_data = [];
            $data->job_city_data = [];
            $data->job_province_data = [];
        }
        return $this->success($data);
    }

    /**
     * 我的面试
     *
     * @param Request $request
     * @return InerviewController
     */
    public function myInterList(Request $request)
    {
        $page = $request->get('page', 1);
        $pageSize = $request->get('pageSize', 20);
        $company_name = $request->get('company_name', '');
        $type = $request->get('type', 0);
        $start_time = $request->get('start_time', '');
        $end_time = $request->get('end_time', '');


        $member = Member::where('token', $request->token)->first();
        $list = Interview::from('interview as a')
//            ->leftjoin('members_info as b','a.mid','=','b.mid')
//            ->leftjoin('members as c','a.mid','=','c.id')
            ->leftjoin('companys as d', 'a.cid', '=', 'd.id')
            ->leftjoin('job as e', 'a.jid', '=', 'e.id')
            ->where('a.mid', $member->id);
//            ->where('a.status','>',0);
        if ($company_name) {
            $list = $list->where('d.company_name', 'like', '%' . $company_name . '%');
        }
        if ($start_time && $end_time) {
            $list = $list->whereBetween('a.inte_time', [$start_time . ' 00:00:00', $end_time . ' 23:59:59']);
        }
        if ($type) {
            switch ($type) {
                case 1:
                    $list = $list->whereIn('a.status', [2]);
                    break;
                case 2:
                    $list = $list->whereIn('a.status', [3, 4, 5, 6]);
                    break;
                case 3:
                    $list = $list->whereIn('a.status', [7, 8]);
                    break;
            }
        }
        $count = ceil($list->count() / $pageSize);
        $list = $list->orderBy('a.id', 'desc')
            ->offset(($page - 1) * $pageSize)
            ->limit($pageSize)
            ->get(['a.*', 'd.company_name', 'd.logo', 'e.name as job_name', 'e.work_type',
                'e.sex',
                'e.student_age',
                'e.pay',
                'e.money_type',
                'e.pay_type',
                'e.job_city',
                'e.type',
                'e.job_type',
                'e.language',
                'e.job_week_day',
                'e.job_day_time',
                'e.edu_type',
                'e.cert',
                'e.job_year',
                'e.job_info',
                'e.benefits']);
        foreach ($list as $k => $v) {
            $v->logo_path = Files::where('id', $v->logo)->first();
            if ($v->job_city) {
                $v->job_area_data = Region::find($v->job_city);
                $v->job_city_data = Region::find($v->job_area_data->pid);
                $v->job_province_data = Region::find($v->job_city_data->pid);
            } else {
                $v->job_area_data = [];
                $v->job_city_data = [];
                $v->job_province_data = [];
            }
        }
        return $this->success(['count' => $count, 'list' => $list, 'page' => $page]);
    }

    /**
     * 面试进度
     *
     * @param Request $request
     * @return InerviewController
     */
    public function interSpeed(Request $request)
    {
        $id = $request->get('id', 0);
        if (!$id) {
            return $this->fail(100001);
        }
        $list = InterviewLogs::with(['interview' => function ($query) {
            $query->select(['id', 'inte_time']);
        }])->where('vid', $id)->orderBy('id', 'desc')->get(['id', 'info', 'created_at', 'vid']);
        return $this->success($list);
    }

    /**
     * 我的面试日历
     * @param Request $request
     * @return InerviewController
     */
//    public function myDayList(Request $request){
//        $date       =   $request->get('date',date('Y-m'));
//        $comany_name       =   $request->get('company_name','');
//        $member = Member::where('token',$request->token)->first();
//        $list = Interview::from('interview as a')
////            ->leftjoin('members_info as b','a.mid','=','b.mid')
//            ->leftjoin('companys as c','a.cid','=','c.id')
//            ->leftjoin('job as e','a.jid','=','e.id')
//            ->whereRaw(DB::raw('left(a.inte_time,7)="'.$date.'"'))
//            ->where('a.status','>',0)
//            ->where('a.mid',$member->id);
//        if($comany_name){
//            $list = $list->where('c.company_name','like','%'.$comany_name.'%');
//        }
//        $list = $list->groupBy('time')
//            ->orderBy('time','asc')
//            ->get([DB::raw('count(a.id) as count'),DB::raw('left(a.inte_time,10) as time')]);
//        foreach ($list as $k => $v){
//            $list1 = Interview::from('interview as a')
////                ->leftjoin('members_info as b','a.mid','=','b.mid')
//                ->leftjoin('companys as c','a.cid','=','c.id')
//                ->leftjoin('job as e','a.jid','=','e.id')
//                ->whereRaw(DB::raw('left(a.inte_time,10)="'.$v->time.'"'))
//                ->where('a.status','>',0)
//                ->where('a.mid',$member->id);;
//            if($comany_name){
//                $list1 = $list1->where('c.company_name','like','%'.$comany_name.'%');
//            }
//            $v->list = $list1->get(['a.*','c.logo','c.company_name','e.name as job_name','e.pay','e.money_type','e.pay_type','e.job_city','e.type']);
//            foreach ($v->list as $k1 => $v1){
//                $ad_list = CompanyAdvier::where('cid',$v1->cid)->get(['uid']);
//                $v1->company_advier  = User::whereIn('id',$ad_list)->get(['id','name']);
//                $v1->logo_path = Files::whereIn('id',explode(',',$v1->logo))->get();
//                if($v1->job_city){
//                    $v1->job_area_data = Region::find($v1->job_city);
//                    $v1->job_city_data = Region::find($v1->job_area_data->pid);
//                    $v1->job_province_data  = Region::find($v1->job_city_data->pid);
//                }else{
//                    $v1->job_area_data =  [];
//                    $v1->job_city_data  = [];
//                    $v1->job_province_data  = [];
//                }
//
//            }
//        }
//        return $this->success(['list' => $list,'time' => date('Y-m-d H:i:00')]);
//    }
    /**
     * 我的面试记录
     *
     * @param Request $request
     * @return InerviewController
     */
    public function myLogList(Request $request)
    {
        $start_time = $request->get('start_time', '');
        $end_time = $request->get('end_time', '');
        $page = $request->get('page', 1);
        $pageSize = $request->get('pageSize', 6);

        $list = Interview::from('interview as a')
//            ->leftjoin('members_info as b','a.mid','=','b.mid')
            ->leftjoin('companys as c', 'c.id', '=', 'a.cid')
            ->leftjoin('job as e', 'a.jid', '=', 'e.id');
        if ($start_time && $end_time) {
            $list = $list->whereBetween('a.inte_time', [$start_time . ' 00:00:00', $end_time . ' 23:59:59']);
        }
        $list = $list->whereIn('a.status', [3, 4, 5, 6]);
        $count = ceil($list->count() / $pageSize);
        $list = $list->orderBy('a.id', 'desc')
            ->offset(($page - 1) * $pageSize)
            ->limit($pageSize)
            ->get(['a.*', 'c.logo', 'c.company_name', 'e.name as job_name', 'e.pay', 'e.money_type', 'e.pay_type', 'e.job_city', 'e.type']);
        foreach ($list as $k => $v) {
            $ad_list = CompanyAdvier::where('cid', $v->cid)->get(['uid']);
            $v->company_advier = User::whereIn('id', $ad_list)->get(['id', 'name']);
            $v->logo_path = Files::whereIn('id', explode(',', $v->logo))->get();
            if ($v->job_city) {
                $v->job_area_data = Region::find($v->job_city);
                $v->job_city_data = Region::find($v->job_area_data->pid);
                $v->job_province_data = Region::find($v->job_city_data->pid);
            } else {
                $v->job_area_data = [];
                $v->job_city_data = [];
                $v->job_province_data = [];
            }

        }
        return $this->success(['count' => $count, 'list' => $list, 'page' => $page]);
    }


    /**
     * 面试进度
     * @param Request $request
     * @return InerviewController
     */
    public function interviewSpeed(Request $request)
    {
        $id = $request->get('id', 0);
        if (!$id) {
            return $this->fail(100001);
        }
        $list = InterviewLogs::with(['interview' => function ($query) {
            $query->select(['id', 'inte_time']);
        }])->where('vid', $id)->orderBy('id', 'desc')->get(['id', 'info', 'created_at', 'vid']);
        return $this->success($list);
    }


    /**
     * 面试详情
     * @param Request $request
     * @return InerviewController
     */
    public function interviewDesc(Request $request)
    {
        $id = Input::get('id', 0);
        $token = $request->get('token', '');
        $company = Companys::where('token', $token)->first();

        if (!$id) {
            return $this->fail(100001);
        }
//        $data = MemberInfoChecked::from('members_info_checked as a')
        $data = Interview::from('interview as a')
            ->leftjoin('members_info as b', 'a.mid', '=', 'b.mid')
            ->where('a.id', $id)
            ->first();
        if (!$data) {
            return $this->fail(2000005);
        } else {
            $data->interview_status = $data->status;
            $country = Country::find($data->nationality);
            $data['nationality_val'] = $country['code'];
            $data->videos_path = null;
            if ($data->videos) {
                $data->videos_path = Files::whereIn('id', explode(',', $data->videos))->get();
            }
            if ($data->photos) {
                $data->photos_path = Files::where('id', $data->photos)->first();
            }
            $data->working_city_datas = null;
            if ($data->working_city) {
                $city_arr = explode(',', $data->working_city);
                $citys = [];
                if(count($city_arr)){
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
            }
            $data->china_address_city_data = null;
            if ($data->china_address && $data->in_domestic == 1) {
                $tmp_city = Region::find($data->china_address);
                $tmp_pro = Region::find($tmp_city->pid);
                $data->china_address_city_data = [
                    'province_data' => $tmp_pro,
                    'city_data' => $tmp_city,
                ];
            }
            $data->edu_cert_imgs_path = null;
            if ($data->edu_cert_imgs) {
                $data->edu_cert_imgs_path = Files::whereIn('id', explode(',', $data->edu_cert_imgs))->get();
            }
            $data->edu_auth_imgs_path = null;
            if ($data->edu_auth_imgs) {
                $data->edu_auth_imgs_path = Files::whereIn('id', explode(',', $data->edu_auth_imgs))->get();
            }
            $data->notes_path = null;
            if ($data->notes) {
                $data->notes_path = Files::whereIn('id', explode(',', $data->notes))->get();
            }

            $data->invite = [];
            if ($data->invite_code) {
                $data->invite = Invite::where('code', $data->invite_code)->first(['name', 'phone', 'email']);
            }
            return $this->success($data);
        }
    }

    /**
     * 面试记录
     * @param Request $request
     * @return InerviewController
     */
    public function logList(Request $request)
    {
        $token = $request->get('token');
        $start_time = $request->get('start_time', '');
        $end_time = $request->get('end_time', '');
        $page = $request->get('page', 1);
        $pageSize = $request->get('pageSize', 6);
        $company = Companys::where('token', $token)->first();

        $list = Interview::from('interview as a')
            ->leftjoin('members_info as b', 'a.mid', '=', 'b.mid');
        if ($start_time && $end_time) {
            $list = $list->whereBetween('a.inte_time', [$start_time . ' 00:00:00', $end_time . ' 23:59:59']);
        }
        $list = $list->where('a.cid', $company->id)
            ->whereIn('a.status', [3, 4, 5, 6]);
        $count = ceil($list->count() / $pageSize);
        $list = $list->orderBy('a.id', 'desc')
            ->offset(($page - 1) * $pageSize)
            ->limit($pageSize)
            ->get(['a.*', 'b.*', 'b.id as info_mid']);
        foreach ($list as $k => $v) {
            $v->photos_path = Files::whereIn('id', explode(',', $v->photos))->get();
            $country = Country::where('id', $v->nationality)->first();
            $v->nationality_val = $country['code'];
        }
        return $this->success(['count' => $count, 'list' => $list, 'page' => $page]);
    }

    public function xcx_daylist(Request $request)
    {
        $token = $request->get('token');
        $date = $request->get('date', date('Y-m-d'));
        $name = $request->get('name', '');
        $company = Companys::where('token', $token)->first();
        $list = Interview::from('interview as a')
            ->leftjoin('members_info as b', 'a.mid', '=', 'b.mid')
            ->where('a.cid', $company->id)
            ->whereRaw(DB::raw('left(a.inte_time,10)="' . $date . '"'));
        $name && $list = $list->where('b.name', 'like', '%' . $name . '%');
        $list = $list->where('a.cid', $company->id)->get(['a.*', 'b.*', 'a.id as id', 'b.id as info_mid']);
        foreach ($list as $k1 => $v1) {
            $country = Country::find($v1['nationality']);
            $v1->nationality_val = $country['code'];
            $v1->country_val = null;
            if ($v1->country) {
                $country = Country::find($v1['country']);
                $v1->country_val = $country['code'];
            }
            if ($v1->photos) {
                $v1->photos_path = Files::whereIn('id', explode(',', $v1->photos))->get();
            }
        }
        return $this->success(['list' => $list, 'time' => date('Y-m-d H:i:00')]);
    }

    /**
     * 面试日程
     * @param Request $request
     * @return InerviewController
     */
    public function daylist(Request $request)
    {
        $token = $request->get('token');
        $date = $request->get('date', date('Y-m'));
        $name = $request->get('name', '');
        $company = Companys::where('token', $token)->first();
        $list = Interview::from('interview as a')
            ->leftjoin('members_info as b', 'a.mid', '=', 'b.mid')
            ->whereRaw(DB::raw('left(a.inte_time,7)="' . $date . '"'))
            ->where('a.cid', $company->id);
        if ($name) {
            $list = $list->where('b.name', 'like', '%' . $name . '%');
        }
        $list = $list->groupBy('time')
            ->orderBy('time', 'asc')
            ->get([DB::raw('count(a.id) as count'), DB::raw('left(a.inte_time,10) as time')]);
        foreach ($list as $k => $v) {
            $list1 = Interview::from('interview as a')
                ->leftjoin('members_info as b', 'a.mid', '=', 'b.mid')
                ->whereRaw(DB::raw('left(a.inte_time,10)="' . $v->time . '"'));
            if ($name) {
                $list1 = $list1->where('b.name', 'like', '%' . $name . '%');
            }
            $v->list = $list1->where('a.cid', $company->id)->get(['a.*', 'b.*', 'a.id as id', 'b.id as info_mid']);
            foreach ($v->list as $k1 => $v1) {
                $country = Country::find($v1['nationality']);
                $v1->nationality_val = $country['code'];
                $v1->country_val = null;
                if ($v1->country) {
                    $country = Country::find($v1['country']);
                    $v1->country_val = $country['code'];
                }
                if ($v1->photos) {
                    $v1->photos_path = Files::whereIn('id', explode(',', $v1->photos))->get();
                }

            }
        }
        return $this->success(['list' => $list, 'time' => date('Y-m-d H:i:00')]);
    }


    /**
     * 人才列表
     * @param Request $request
     * @return InerviewController
     */
    public function listInvite(Request $request)
    {
        $page = $request->get('page', 1);
        $pageSize = $request->get('pageSize', 15);
        $status = $request->get('status', 0);
        $name = $request->get('name', '');
        $page = $page < 1 ? 0 : $page;
        $token = $request->get('token');

        $company = Companys::where('token', $token)->first();
        $list = Interview::from('interview as a')
            ->leftjoin('members_info as b', 'a.mid', '=', 'b.mid')
            ->leftjoin('members as c', 'a.mid', '=', 'c.id')
            ->leftjoin('companys as d', 'a.cid', '=', 'd.id')
            ->where('a.cid', $company->id)
            ->where('a.status', '<>', 2);
        if ($name) {
            $list = $list->where('b.name', 'like', '%' . $name . '%');
        }
        if ($status) {
            if ($status == 7) {
                $list = $list->whereIn('a.status', [6, 7, 8]);
            } else {
                $list = $list->where('a.status', ($status - 1));
            }
        }
        $count = ceil($list->count() / $pageSize);
        $list = $list->orderBy('a.id', 'desc')
            ->offset(($page - 1) * $pageSize)
            ->limit($pageSize)
            ->get(['a.*', 'b.*', 'b.id as info_mid', 'a.id as id']);
        foreach ($list as $k => $v) {
            $country = Country::find($v['nationality']);
            $list[$k]['nationality_val'] = $country['code'];
            $list[$k]['country_val'] = null;
            if ($list[$k]['country']) {
                $country = Country::find($v['country']);
                $list[$k]['country_val'] = $country['code'];
            }
            if ($v->photos) {
                $list[$k]['photos_path'] = Files::whereIn('id', explode(',', $v->photos))->get();
            }
        }
        return $this->success(['count' => $count, 'list' => $list, 'page' => $page]);
    }

    /**
     * 修改面试
     * @param Request $request
     * @return InerviewController
     */
    public function updateInvite(Request $request)
    {
        $id = $request->get('id', 0);
        $time = $request->get('time', '');
        if (!$id || !$time) {
            return $this->fail(100001);
        }
        try {
            DB::beginTransaction();
            $Inter = Interview::where('id', $id)->where('status', '<', 2)->first();
            $update_data = ['inte_time' => $time, 'status' => 0];
            if ($Inter->status == 1) {
                $update_data['up_flg'] = 1;
            } else {
                $update_data['up_flg'] = 0;
            }
            $flg = Interview::where('id', $id)->where('status', '<', 2)->update($update_data);
            $log_model = new InterviewLogs();
            $memberInfo = MemberInfo::where('mid', $Inter->mid)->first();
            $msgData['company_name'] = $request->company->company_name;
            $msgData['teach_name'] = $memberInfo->name . ' ' . $memberInfo->last_name;
            $msgData['time'] = $Inter->inte_time;
            $msgData['time1'] = $time;
            $msg = interViewLogMsg($msgData, 8);
            $log_model->vid = $id;
            $log_model->info = $msg[0];
            $log_model->info1 = $msg[1];
            $flg1 = $log_model->save();
            if ($flg && $flg1) {
                Notice::addNotice(returnNoticeMsg(['company_name' => $msgData['company_name'], 'teach_name' => $msgData['teach_name'], 'time' => $msgData['time'], 'time1' => $msgData['time1']], 3002), 3, 3002);
                DB::commit();
                if ($update_data['up_flg'] == 1) {
                    //发送飞书通知
                    if (config('app.env') == 'production') {
                        //获取运营部通知手机好
                        $phones = $this->getYunYingUserPhone();
                        //获取通知内容
                        $Feishu['company_name'] = $request->company->company_name;
                        $Feishu['time'] = date("Y年m月d日 H:i");
                        $Feishu['time1'] = date("Y年m月d日 H:i", strtotime($time));
                        $this->FeiShuSendText($phones, returnFeiShuMsg(8, $Feishu));
                    }
                }
                return $this->success();
            } else {
                DB::rollback();
                return $this->fail();
            }
        } catch (\Exception $e) {
            DB::rollback();
            Log::info('修改面试失败：' . $e->getMessage());
            return $this->fail();
        }

    }

    /**
     * 取消面试
     * @param Request $request
     * @return InerviewController
     */
    public function cancelInvite(Request $request)
    {
        $id = $request->get('id', 0);
        $token = $request->get('token', '');
        $company = Companys::where('token', $token)->first();
        if (!$id) {
            return $this->fail(100001);
        }
        try {
            DB::beginTransaction();
            $Inter = Interview::where('cid', $company->id)->where('id', $id)->where('status', '<>', 6)->first();
            $flg = Interview::where('cid', $company->id)->where('id', $id)->where('status', '<>', 6)->update(['status' => 6]);
            $log_model = new InterviewLogs();
            $memberInfo = MemberInfo::where('mid', $Inter->mid)->first();
            $msgData['company_name'] = $company->company_name;
            $msgData['teach_name'] = $memberInfo->name . ' ' . $memberInfo->last_name;
            $msgData['time'] = $Inter->inte_time;
            $msg = interViewLogMsg($msgData, 9);
            $log_model->vid = $id;
            $log_model->info = $msg[0];
            $log_model->info1 = $msg[1];
            $flg1 = $log_model->save();
            if ($flg && $flg1) {
                Notice::addNotice(returnNoticeMsg(['company_name' => $msgData['company_name'], 'teach_name' => $msgData['teach_name'], 'time' => $msgData['time']], 3003), 3, 3003);
                DB::commit();
                if (config('app.env') == 'production') {
                    //获取运营部通知手机好
                    $phones = $this->getYunYingUserPhone();
                    $memberInfo = MemberInfo::where('mid', $Inter->mid)->first();
                    //获取通知内容
                    $Feishu['company_name'] = $company->company_name;
                    $Feishu['teach_name'] = $memberInfo->name . ' ' . $memberInfo->last_name;
                    $Feishu['time'] = date("Y年m月d日 H:i", strtotime($Inter->inte_time));
                    $this->FeiShuSendText($phones, returnFeiShuMsg(9, $Feishu));
                }
                return $this->success();
            } else {
                DB::rollback();
                return $this->fail();
            }
        } catch (\Exception $e) {
            DB::rollback();
            Log::info('取消面试失败：' . $e->getMessage());
            return $this->fail();
        }
    }

    /**
     * 创建面试
     * @param Request $request
     * @return InerviewController
     */
    public function invite(Request $request)
    {
        $token = $request->get('token');
        $mid = $request->get('mid', 0);
        $time = $request->get('time', '');
        $jid = $request->get('jid', 0);
        if (!$mid || !$time || !$jid) {
            return $this->fail(100001);
        }
        $company = Companys::where('token', $token)->first();
        $vipaction = null;
        //判断当前是否是会员
        if (!$request->company->vip_actions_id) {//不是会员
            return $this->fail(2000221);
        } else {//会员
            //是否超出当前会员次数 通过会员开始结束时间来判断 该时段是否超出越面次数
            $vipaction = VipAction::find($request->company->vip_actions_id);
            $vip = Vip::find($vipaction->vip_id);
            //超出应聘邀请次数次数
            if ($vipaction->yy_yaoqing >= $vip->yaoqing) {
                return $this->fail(2000222);
            }
        }
        if ($company->sign_flg == 2) {
            return $this->fail(100010);
        }
        //查询职位
        $job = Job::where('cid', $company->id)->where('id',$jid)->where('status', 1)->first();
        if (!$job) {
            return $this->fail(2000203);
        }
        $count = Interview::where('mid', $mid)->whereIn('status', [0, 1, 2])->where('cid', $company->id)->count();
        if ($count) {
            return $this->fail(2000202);
        }
        if (strtotime($time) < time()) {
            return $this->fail(2000210);
        }
        $memberInfo = MemberInfo::where('mid', $mid)->first();
        $member = Member::find($memberInfo->mid);
        $msgData['email'] = $member->email;
        try {
            DB::beginTransaction();
            $model = new Interview();
            $model->mid = $mid;
            $model->inte_time = $time;
            $model->cid = $company->id;
            $model->jid = $jid;
            $flg = $model->save();

            $log_model = new InterviewLogs();
            $msg = interViewLogMsg($msgData, 1);
            $log_model->vid = $model->id;
            $log_model->info = $msg[0];
            $log_model->info1 = $msg[1];
            $flg1 = $log_model->save();
            if ($flg1 && $flg) {
                //增加邀请次数
                if($vipaction){
                    $vipaction->yy_yaoqing  = $vipaction->yy_yaoqing  + 1;
                    $vipaction->save();
                }
                Notice::addNotice(returnNoticeMsg(['company_name' => $company->company_name, 'teach_name' => $memberInfo->name . ' ' . $memberInfo->last_name, 'time' => $time], 3001), 3, 3001);
                if (config('app.env') == 'production') {
                    //获取运营部通知手机好
                    $phones = $this->getYunYingUserPhone();
                    //获取通知内容
                    $Feishu['company_name'] = $company->company_name;
                    $Feishu['teach_name'] = $memberInfo->name . ' ' . $memberInfo->last_name;
                    $Feishu['time'] = date("Y年m月d日 H:i");
                    $this->FeiShuSendText($phones, returnFeiShuMsg(6, $Feishu));
//                    Log::info(json_encode([
//                        'email'     => $member->email,
//                        'template'  => 'mianshi_yaoqing',
//                        'title'     => 'APEX GLOBAL - Job Invitation',
//                        'job_name'  => $job->name,
//                        'company_name' => $company->company_name,
//                        'teach_name' => $memberInfo->last_name,
//                        'time'       => date("H:i Y/m/d",strtotime($time)),
//                    ]));
                    //邮件发送面试邀请
                    dispatch(new SendEmail([
                        'email'     => $member->email,
                        'template'  => 'mianshi_yaoqing',
                        'title'     => 'APEX GLOBAL - Job Invitation',
                        'job_name'  => $job->name,
                        'company_name' => $company->company_name,
                        'teach_name' => $memberInfo->last_name,
                        'time'       => date("H:i Y/m/d",strtotime($time)),
                    ]));
                }
                DB::commit();
                return $this->success();
            } else {
                DB::rollback();
                return $this->fail();
            }
        } catch (\Exception $e) {
            DB::rollback();
            Log::info('添加面试失败：' . $e->getMessage());
            Log::info($e->getLine());
            return $this->fail();
        }
    }

}
