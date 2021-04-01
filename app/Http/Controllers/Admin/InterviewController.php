<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Index\InerviewController;
use App\Jobs\SendWxNotice;
use App\Models\Collect;
use App\Models\CompanyAdvier;
use App\Models\Companys;
use App\Models\Country;
use App\Models\Files;
use App\Models\Interview;
use App\Models\InterviewLogs;
use App\Models\Job;
use App\Models\Member;
use App\Models\MemberInfo;
use App\Models\Notice;
use App\Models\Official;
use App\Models\Region;
use App\Models\Rooms;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use TencentCloud\Common\Credential;
use TencentCloud\Common\Exception\TencentCloudSDKException;
use TencentCloud\Common\Profile\ClientProfile;
use TencentCloud\Common\Profile\HttpProfile;
use TencentCloud\Trtc\V20190722\Models\DismissRoomRequest;
use TencentCloud\Trtc\V20190722\TrtcClient;

class InterviewController extends Controller
{
    public function getInterContract(Request $request){
        $id = $request->get('id','');
        if(!$id){
            return $this->fail(100001);
        }
        $data = Interview::from('interview as a')->leftjoin('companys as b','a.cid','=','b.id')->where('a.id',$id)->first(['b.company_name','b.business_name as b_company_name','b.contact as b_name','b.contact_phone as b_phone']);
        return  $this->success($data ?? null);
    }

    /**
     * 待签约列表
     * @param Request $request
     */
    public function signList(Request $request){
        $page       =   $request->get('page',1);
        $pageSize   =   $request->get('pageSize',15);

        $list = Interview::from('interview as a')
            ->leftjoin('members_info as b','a.mid','=','b.mid')
            ->leftjoin('members as c','a.mid','=','c.id')
            ->leftjoin('companys as d','a.cid','=','d.id')
            ->where('a.status',3);

        $request->name && $list = $list->where('b.name','like',"%{$request->name}%");
        $request->company_name && $list = $list->where('d.company_name','like',"%{$request->company_name}%");
        $count = ceil($list->count()/$pageSize);
        $list = $list->orderBy('id','desc')
            ->offset(($page-1)*$pageSize)
            ->limit($pageSize)
            ->get(['a.id','a.mid','a.cid','d.company_name','b.name','b.last_name','b.nationality','a.inte_time']);
        foreach ($list as $k => $v){
            $country =  Country::find($v->nationality);
            $v->nationality_val = $country['code'];
            $ad_list = CompanyAdvier::where('cid',$v->cid)->get(['uid']);
            $v->company_advier  = User::whereIn('id',$ad_list)->get(['id','name']);
        }
        return $this->success(['count' => $count,'list' => $list,'page' => $page]);
    }
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

    //人才列表
    public function talentList(Request $request){
        $type   = $request->get('type',0);//类型  0面试人才管理 1收藏藏候选管理
        $status = $request->get('status',0);//状态
        $id     = $request->get('id',0);//公司id
        $page       =   $request->get('page',1);
        $pageSize   =   $request->get('pageSize',15);
        $page       =   $page<1 ? 0 : $page;
        if($type == 0){
            $list =  Interview::from('interview as a')
                ->leftjoin('members_info as b','a.mid','=','b.mid')
                ->leftjoin('members as c','a.mid','=','c.id')
                ->leftjoin('companys as d','a.cid','=','d.id');
            if($id){
                $list = $list->where('d.id',$id);
            }
            if($status){
                if($status == 7){
                    $list = $list->whereIn('a.status',[6,7,8,9]);
                }else{
                    $list = $list->where('a.status',($status-1));
                }
            }
            $count = ceil($list->count()/$pageSize);
            $list = $list->orderBy('a.id','desc')
                ->offset(($page-1)*$pageSize)
                ->limit($pageSize)
                ->get(['a.*','b.name','b.last_name','b.photos','c.id as mid','b.nationality','b.id as info_id','a.id as id','d.company_name','b.working_seniority','d.logo']);
            foreach ($list as $k => $v){
                $country =  Country::find($v->nationality);
                $v->nationality_val = $country['code'];
                $v->photos_path = Files::whereIn('id',explode(',',$v->photos))->get();
                $ad_list = CompanyAdvier::where('cid',$v->cid)->get(['uid']);
                $v->company_advier  = User::whereIn('id',$ad_list)->get(['id','name']);
                $v->logo_path = Files::whereIn('id',explode(',',$v->logo))->get();
            }
        }else{
            $list =  MemberInfo::from('collect as a')
                ->leftjoin('members_info as b','a.mid','=','b.mid')
                ->leftjoin('members as c','a.mid','=','c.id')
                ->leftjoin('companys as d','a.cid','=','d.id');
            if($id){
                $list = $list->where('d.id',$id);
            }
            $count = ceil($list->count()/$pageSize);
            $list = $list->orderBy('a.id','desc')->offset(($page-1)*$pageSize)->limit($pageSize)->get(['a.*','b.name','b.last_name','b.photos','c.id as mid','b.nationality','b.id as info_id','a.id as id','d.company_name','b.working_seniority','d.logo']);
            foreach ($list as $k => $v){
                /*$country =  Country::find($v->nationality);
                $v->nationality_val = $country['code'];
                $v->photos_path = Files::whereIn('id',explode(',',$v->photos))->get();*/
                $ad_list = CompanyAdvier::where('cid',$v->cid)->get(['uid']);
                $v->company_advier  = User::whereIn('id',$ad_list)->get(['id','name']);
//                $v->logo_path = Files::whereIn('id',explode(',',$v->logo))->get();

                $v->logo_path = $this->getDefaultLogo(2);
                if($v->logo){
                    $v->logo_path = Files::whereIn('id',explode(',',$v->logo))->get();
                }
                $country =  Country::find($v['nationality']);
                $list[$k]['nationality_val'] = $country['code'];
                $list[$k]['country_val'] =  null;
                if($list[$k]['country']){
                    $country =  Country::find($v['country']);
                    $list[$k]['country_val'] = $country['code'];
                }
                if($v->photos){
                    $list[$k]['photos_path']  = Files::whereIn('id',explode(',',$v->photos))->get();
                }
            }
        }

        return $this->success(['count' => $count,'list' => $list,'page' => $page]);
    }

    //面试管理
    public function list(Request $request){
        $status         = $request->get('status',0);
        $teach_name     = $request->get('teach_name','');
        $company_name   = $request->get('company_name','');
        $page       =   $request->get('page',1);
        $pageSize   =   $request->get('pageSize',15);
        $page       =   $page<1 ? 0 : $page;

        $list =  Interview::from('interview as a')
            ->leftjoin('members_info as b','a.mid','=','b.mid')
            ->leftjoin('members as c','a.mid','=','c.id')
            ->leftjoin('companys as d','a.cid','=','d.id');
        switch ($status){
            case 1:
                $list = $list->whereIn('a.status',[0]);
                break;
            case 2:
                $list = $list->whereIn('a.status',[1]);
                break;
            case 3:
                $list = $list->whereIn('a.status',[2]);
                break;
            case 4:
                $list = $list->whereIn('a.status',[3,4,5,6]);
                break;
            case 5:
                $list = $list->whereIn('a.status',[7,8,9]);
                break;
        }
           /*1 0待确认
           2 1待面试
           3 2面试中
           4 3456已完成
           5 78已结束*/
        if($teach_name){
            $list = $list->where('b.name','like',"%{$teach_name}%");
        }
        if($company_name){
            $list = $list->where('d.company_name','like',"%{$company_name}%");
        }
        $count = ceil($list->count()/$pageSize);
        $list = $list->orderBy('a.id','desc')
            ->offset(($page-1)*$pageSize)
            ->limit($pageSize)
            ->get(['a.*','b.name','b.last_name','c.id as mid','b.nationality','b.id as info_id','a.id as id','d.company_name','b.working_seniority','d.logo']);
        foreach ($list as $k => $v){
            $country =  Country::find($v->nationality);
            $v->nationality_val = $country['code'];

            $ad_list = CompanyAdvier::where('cid',$v->cid)->get(['uid']);
            $v->company_advier  = User::whereIn('id',$ad_list)->get(['id','name']);
//            $v->logo_path = Files::whereIn('id',explode(',',$v->logo))->get();
            $v->logo_path = $this->getDefaultLogo(2);
            if($v->logo){
                $v->logo_path = Files::whereIn('id',explode(',',$v->logo))->get();
            }
            /*if($v->status == 0){
                $v->status = 1;
            }elseif($v->status == 1){
                $v->status = 2;
            }elseif($v->status == 2){
                $v->status = 3;
            }elseif(in_array($v->status,[3,4,5,6])){
                $v->status = 4;
            }elseif(in_array($v->status,[7,8])){
                $v->status = 5;
            }*/
        }
        return $this->success(['count' => $count,'list' => $list,'page' => $page]);
    }

    /**
     * 参加/拒绝/变更 面试接口
     *
     * @param Request $request
     * @return InterviewController
     */
    public function joinInterview(Request $request){
        $id     = $request->get('id','');
        $type   = $request->get('type',1);//1参加 2不参加 3变更
        $time   = $request->get('time','');//修改面试时间
        if(!$id){
            return $this->fail(2000001);
        }
        try{
            DB::beginTransaction();
            $model = Interview::find($id);
            $teach = MemberInfo::where('mid',$model->mid)->first();
            $teach_account = Member::find($model->mid);
            $company = Companys::find($model->cid);
            $company_adviser = CompanyAdvier::where('cid',$company->id)->first();
            $user = User::find($company_adviser->uid);
            $emailData['time'] = date("H:i Y/m/d",strtotime($model->inte_time));
            $emailData['time1']= date("H:i Y/m/d",strtotime($time));
            $modelLog = new InterviewLogs();
            $modelLog->vid = $id;
            $msgData['company_name'] = $company->company_name;
            $msgData['teach_name'] = $teach->name . ' ' .$teach->last_name;
            $msgData['time'] = $model->inte_time;
            $modelLog->vid     = $model->id;
            $msgData['adviser_name'] = $user->name;
            if($model->status > 1){
                return $this->fail(2000001);
            }
            switch ($type){
                case 1:
                    $model->status = 1;
                    $msg = interViewLogMsg($msgData,2);
                    break;
                case 2:
                    $model->status = 7;
                    $msg = interViewLogMsg($msgData,3);
                    break;
                case 3:
                    if(!$time){
                        return $this->fail(2000001);
                    }
                    $msgData['time1'] = $time;
                    $model->status = 1;
                    $model->inte_time = $time;
                    $msg = interViewLogMsg($msgData,4);
                    break;
            }
            $modelLog->info    = $msg[0];
            $modelLog->info1    = $msg[1];
            if($modelLog->save() && $model->save()){
                $type_email_temp = '';
                $emailData['teach_name'] = $teach->name . ' ' .$teach->last_name;
                $emailData['company_name'] = $company->company_name;
                $email = $teach_account->email;
                switch ($type){
                    case 1://确认面试
                        Notice::addNotice(returnNoticeMsg(['adviser_name' => $user->name ,'teach_name' => $teach->name . ' ' .$teach->last_name,'company_name' => $company->company_name,'time' =>$emailData['time']],3006),3,3006);
                        $type_email_temp = 'email.mianshi_tongzhi';
                        break;
                    case 2://取消面试
                        Notice::addNotice(returnNoticeMsg(['adviser_name' => $user->name ,'teach_name' => $teach->name . ' ' .$teach->last_name,'company_name' => $company->company_name,'time' => $emailData['time']],3007),3,3007);
                        $type_email_temp = 'email.mianshi_quxiao';
                        break;
                    case 3://修改面试时间
                        Notice::addNotice(returnNoticeMsg(['adviser_name' => $user->name ,'teach_name' => $teach->name . ' ' .$teach->last_name,'company_name' => $company->company_name,'time' => $emailData['time'],'time1' => $emailData['time1']],3008),3,3008);
                        $type_email_temp = 'email.mianshi_xiugai';
                        break;
                }
                //发送审核失败微信通知
                if($company->unionid && config('app.env') == 'production'){
                    $officials = Official::where('unionid',$company->unionid)->where('status',1)->first();
                    if($officials) {
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
                        }elseif($type == 2){
                            $job = Job::find($model->jid);
                            //发送微信通知
                            $wxNoticeData = [
                                'openid' => $officials->openid,
                                'type' => 6,
                                'title' => '您预约的面试已被拒绝。',
                                'memo'  => '有疑问请联系您的顾问。',
                                'key' => [
                                    'keyword1' => $company->company_name,
                                    'keyword2' => $job->name,
                                    'keyword3' => date("Y年m月d日 H:i",strtotime($model->inte_time)),
                                ],
                            ];
                            $this->dispatch(new SendWxNotice($wxNoticeData));
                        }elseif($type == 3){
                            //发送微信通知
                            $wxNoticeData = [
                                'openid' => $officials->openid,
                                'type' => 2,
                                'title' => '您预约的面试已被变更，请安排好时间准时参加面试。',
                                'memo'  => '请在电脑端准时参加面试。',
                                'key' => [
                                    'keyword1' => rand(1000000,99999999),
                                    'keyword2' => '外教面试',
                                    'keyword3' => date("Y年m月d日 H:i",strtotime($time)),
                                ],
                            ];
                            $this->dispatch(new SendWxNotice($wxNoticeData));
                        }
                    }
                }
                Mail::send($type_email_temp,['emailData' => $emailData],function($message)use($email){
                    $message ->to($email)->subject('寰球阿帕斯');
                });
                DB::commit();
                return $this->success();
            }else{
                DB::rollback();
                return $this->fail();
            }
        }catch (\Exception $e){
            Log::info($e->getMessage());
            DB::rollback();
            return $this->fail();
        }
    }


    /**
     * 面试进度
     * @param Request $request
     * @return InerviewController
     */
    public function interviewSpeed(Request $request){
        $id = $request->get('id',0);
        if(!$id){
            return $this->fail(100001);
        }
        $list = InterviewLogs::with(['interview' => function($query){
            $query->select(['id','inte_time']);
        }])->where('vid',$id)->orderBy('id','desc')->get(['id','info1 as info','created_at','vid']);
        return $this->success($list);
    }

    /**
     * 面试日程
     * @param Request $request
     * @return InerviewController
     */
    public function daylist(Request $request){
        $token      =   $request->get('token');
        $date       =   $request->get('date',date('Y-m'));
        $name       =   $request->get('name','');
        $comany_name       =   $request->get('company_name','');
        $company = Companys::where('token',$token)->first();
        $list = Interview::from('interview as a')
            ->leftjoin('members_info as b','a.mid','=','b.mid')
            ->leftjoin('companys as c','a.cid','=','c.id')
            ->whereRaw(DB::raw('left(a.inte_time,7)="'.$date.'"'));
        if($name){
            $list = $list->where('b.name','like','%'.$name.'%');
        }
        if($comany_name){
            $list = $list->where('c.company_name','like','%'.$comany_name.'%');
        }
        $list = $list->groupBy('time')
            ->orderBy('time','asc')
            ->get([DB::raw('count(a.id) as count'),DB::raw('left(a.inte_time,10) as time')]);
        foreach ($list as $k => $v){
            $list1 = Interview::from('interview as a')
                ->leftjoin('members_info as b','a.mid','=','b.mid')
                ->leftjoin('companys as c','a.cid','=','c.id')
                ->whereRaw(DB::raw('left(a.inte_time,10)="'.$v->time.'"'));
            if($name){
                $list1 = $list1->where('b.name','like','%'.$name.'%');
            }
            if($comany_name){
                $list1 = $list1->where('c.company_name','like','%'.$comany_name.'%');
            }
            $v->list = $list1->get(['a.*','b.name','b.last_name','b.nationality','b.photos','b.university','b.working_seniority','pay_type','c.company_name','a.id as id','c.logo']);
            foreach ($v->list as $k1 => $v1){
                $ad_list = CompanyAdvier::where('cid',$v1->cid)->get(['uid']);
                $v1->company_advier  = User::whereIn('id',$ad_list)->get(['id','name']);
                if($v1->photos){
                    $v1->photos_path = Files::whereIn('id',explode(',',$v1->photos))->get();
                }
                $country = Country::where('id',$v1->nationality)->first();
                $v1->nationality_val = $country['code'];
                $v1->logo_path = $this->getDefaultLogo(2);
                if($v1->logo){
                    $v1->logo_path = Files::whereIn('id',explode(',',$v1->logo))->get();
                }

            }
        }
        return $this->success(['list' => $list,'time' => date('Y-m-d H:i:00')]);
    }

    /**
     * 面试记录
     * @param Request $request
     * @return InerviewController
     */
    public function logList(Request $request){
        $start_time =   $request->get('start_time','');
        $end_time   =   $request->get('end_time','');
        $page       =   $request->get('page',1);
        $pageSize   =   $request->get('pageSize',6);

        $list = Interview::from('interview as a')
            ->leftjoin('members_info as b','a.mid','=','b.mid')
            ->leftjoin('companys as c','c.id','=','a.cid');
        if($start_time && $end_time){
            $list = $list->whereBetween('a.inte_time',[$start_time.' 00:00:00',$end_time.' 23:59:59']);
        }
        $list = $list->whereIn('a.status',[3,4,5,6]);
        $count = ceil($list->count()/$pageSize);
        $list = $list->orderBy('a.id','desc')
            ->offset(($page-1)*$pageSize)
            ->limit($pageSize)
            ->get(['a.*','b.name','b.last_name','b.nationality','b.photos','b.university','b.working_seniority','pay_type','c.company_name','a.id as id','c.logo']);
        foreach ($list as $k => $v){
            $ad_list = CompanyAdvier::where('cid',$v->cid)->get(['uid']);
            $v->company_advier  = User::whereIn('id',$ad_list)->get(['id','name']);
//            $v->photos_path    = Files::whereIn('id',explode(',',$v->photos))->get();
            $country = Country::where('id',$v->nationality)->first();
            $v->nationality_val = $country['code'];
            if($v->photos){
                $v->photos_path = Files::whereIn('id',explode(',',$v->photos))->get();
            }
            $v->logo_path = $this->getDefaultLogo(2);
            if($v->logo){
                $v->logo_path = Files::whereIn('id',explode(',',$v->logo))->get();
            }
//            $v->logo_path = Files::whereIn('id',explode(',',$v->logo))->get();

        }
        return $this->success(['count' => $count,'list' => $list,'page' => $page]);
    }
}
