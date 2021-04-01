<?php

namespace App\Http\Controllers\Admin;

use App\Jobs\SendWxNotice;
use App\Models\Checked;
use App\Models\CheckedResult;
use App\Models\Companys;
use App\Models\Country;
use App\Models\Event;
use App\Models\Files;
use App\Models\ImUser;
use App\Models\Invite;
use App\Models\Job;
use App\Models\Member;
use App\Models\MemberAdviser;
use App\Models\MemberInfo;
use App\Models\MemberInfoChecked;
use App\Models\MemberInfoCheckedLog;
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

class MemberController extends Controller
{
    private $mail;
    public function __construct(Mail $mail)
    {
        $this->mail = $mail;
    }

    public function checkEdit(Request $request){
        $data = $request->all();
        if(!isset($request->mid)){
            return $this->fail(100001);
        }
        DB::beginTransaction();
        try {
            $member = Member::find($request->mid);
            $memeber_info_checked =  MemberInfoChecked::where('mid',$request->mid)->first();
            if($request->email || $request->nick_name){
                if($request->email){
                    //查询是否存在
                    $flg = Member::where('email',$request->email)->where('id','<>',$request->mid)->count();
                    if($flg){
                        return $this->fail(100003);
                    }
                }
                $member->update($request->only(['email','nick_name']));
            }

            $data['status'] = 2;
            $data['submit_time'] = date("Y-m-d H:i:s");
            if(!$memeber_info_checked){
                $memeber_info_checked = new MemberInfoChecked();
                $memeber_info_checked->create($data);
            }else{
                $memeber_info_checked->update($data);
            }
            if($request->education){
                $memeber_info_checked->education()->delete();
                $memeber_info_checked->education()->createMany(json_decode($request->education,true));
            }
            if($request->work_experiences){
                $memeber_info_checked->work()->delete();
                $memeber_info_checked->work()->createMany(json_decode($request->work_experiences, true));
            }
            $member_info = MemberInfo::where('mid',$request->mid)->first();
            if(!$member_info){
                $member_info = new MemberInfo();
                $member_info->create($data);
            }else{
                $member_info->update($data);
            }

            //创建顾问
            MemberAdviser::create(['mid' => $request->mid, 'uid' => $request->user->id]);
            $this->dispatch(new \App\Jobs\JobMate(['mid' => $memeber_info_checked->mid , 'type' => 1 ]));
            DB::commit();
            return $this->success();
        }catch (\Exception $e){
            DB::rollBack();
            Log::error($e->getLine());
            Log::error($e->getTrace());
            $this->fail();
        }
    }


    /**
     * 外交入驻列表
     * @return MemberController
     */
    public function checkList(){
        $name       = Input::get('name','');
        $email      = Input::get('email','');
        $phone      = Input::get('phone','');
        $passport   = Input::get('passport','');
        $status     = Input::get('status',0);
        $work_flg   = Input::get('work_flg',0);
        $page   = Input::get('page',1);
        $pageSize = Input::get('pageSize',config('admin.pageSize'));
        if($page<1) $page = 1;
       /* $list = MemberInfoChecked::from('members_info_checked as a')
            ->rightjoin('members as b','a.mid','=','b.id');*/
        $list = Member::from('members as b')
            ->leftjoin('members_info_checked as a','a.mid','=','b.id');
        if($status !== ''){
            $list = $list->where('a.status',$status);
        }
        if($work_flg){
            $list = $list->where('a.work_flg',$work_flg==1 ? 0 : 1);
        }
        if($name){
            $list = $list->where('a.name','like','%'.$name.'%');
        }
        if($email){
            $list = $list->where('b.email','like',"%{$email}%");
        }
        if($phone){
            $list = $list->where('a.phone','like','%'.$phone.'%');
        }
        $count = ceil($list->count()/$pageSize);
        $list = $list->orderBy('b.created_at','desc')->offset(($page-1)*$pageSize)->limit($pageSize)->get(['a.*','b.email','b.nick_name','b.invite_code','b.created_at as created_at']);
        foreach ($list as $k => $v){
            $country =  Country::find($v['nationality']);
            $list[$k]['nationality_val'] = $country['code'];
            $v->invite = [];
            if($v->invite_code){
                $v->invite = Invite::where('code',$v->invite_code)->first(['name', 'phone','email']);
            }
        }
        return $this->success(['count' => $count,'list' => $list,'page' => $page]);
    }

    /**
     * 外教详情查看
     * @return MemberController
     */
    public function checkView(){
        $id     = Input::get('id',0);
        if(!$id){
            return $this->fail(100001);
        }
        $model = MemberInfoChecked::where('mid', $id)->with([
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
            'university_img_path',
            'member' => function ($q){
                $q->select(['id','email','nick_name']);
            }
        ])->first();
        $model->working_city_datas = null;
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
    }

    /**
     * 审核驳回
     * @return MemberController
     */
    public function checkReject(Request $request){
        $id     = Input::get('id',0);
        $info   = Input::get('info','');
        $token  = Input::get('token','');

        if(!$id){
            return $this->fail(100001);
        }
        $data = MemberInfoChecked::where('status',1)->where('id',$id)->first();
        if(!$data){
            return $this->fail(2000005);
        }
        try{
            $user = User::where('token',$token)->first();
            //开启事物
            DB::beginTransaction();
            $res_id = MemberInfoCheckedLog::addLog($data->mid,2,$info,$user->id);
            if($res_id){
                $up_1 = MemberInfoChecked::where('id',$id)->update(['status' => 3,'check_log_id' => $res_id]);
                if($up_1){
                    Event::addEvent($request->user->name.' 驳回入驻申请',$data->mid);
                    Notice::addNotice(returnNoticeMsg(['user' => $request->user->name,'teach_name' => $data->name.' '.$data->last_name],1007),1,1007);
                    DB::commit();
                    $member = Member::find($data->mid);
                    $email = $member->email;
                    $this->mail::send('email.checkFail',['info' => $info,'web_url' => config('app.teach_url')],function($message)use($email){
                        $message ->to($email)->subject('寰球阿帕斯');
                    });
                    return $this->success();
                }else{
                    DB::rollback();
                    return $this->fail();
                }
            }else{
                DB::rollback();
                return $this->fail();
            }
        }catch (\Exception $e){
            Log::info('审核驳回出错了：'.$e->getMessage());
            DB::rollback();
            return $this->fail();
        }
    }

    /**
     * 审核通过
     * @return MemberController
     */
    public function checkOk(Request $request){
        $id     = Input::get('id',0);
        $info   = Input::get('info','');
        $token  = Input::get('token','');

        if(!$id){
            return $this->fail(100001);
        }
        $data = MemberInfoChecked::where('status',1)->where('id',$id)->first();
        if(!$data){
            return $this->fail(2000005);
        }
        try{
            $user = User::where('token',$token)->first();
            //开启事物
            DB::beginTransaction();
            $res_id = MemberInfoCheckedLog::addLog($data->mid,1,$info,$user->id);
            if($res_id){
                $up_1 = MemberInfoChecked::where('id',$id)->update(['status' => 2,'check_log_id' => $res_id]);

                $model = MemberInfo::where('mid',$data->mid)->first();
                if(!$model){
                    $model = new MemberInfo();
                }
                unset($data['id']);
                unset($data['status']);
                unset($data['check_log_id']);
                unset($data['created_at']);
                unset($data['updated_at']);
                unset($data['submit_time']);
                $data1 = $data->toArray();
                foreach ($data1 as $k => $v){
                    $model->$k = $v;
                }
                if($up_1 && $model->save()){
                    $member = Member::find($data->mid);
                    Event::addEvent($request->user->name.' 通过入驻申请',$data->mid);
                    Notice::addNotice(returnNoticeMsg(['user' => $request->user->name,'teach_name' => $data->name.' '.$data->last_name],1005),1,1005);

                    $email = $member->email;
                    $this->mail::send('email.checkSuccess',['web_url' => config('app.teach_url')],function($message)use($email){
                        $message ->to($email)->subject('寰球阿帕斯');
                    });
                    //添加顾问
                    $count = MemberAdviser::where('mid',$member->id)->count();
                    if($count){
                        MemberAdviser::where('mid',$member->id)->update(['uid'=> $user->id]);
                    }else{
                        MemberAdviser::create(['uid'=> $user->id,'mid'=>$member->id]);
                    }
                    //发送消息
                    $imUser = ImUser::where('user_id',$data->mid)->where('type',1)->first();
                    //修改昵称 导入头像
                    $res = $this->createImOneAccount([
                        'Identifier'=>config('app.env').'_'.$imUser->id,
                        'Nick'=>$model->first_name.' '.$model->last_name,
                        'FaceUrl'=> $data->photos ? Files::where('id',$data->photos)->pluck('path')->first() : $this->getDefaultLogo($data->sex == '0' ? 4 : 5)[0]['path'],
                    ]);
                    $res = json_decode($res,true);
                    if($res['ActionStatus'] != 'OK'){
                        DB::rollback();
                        Log::info('IM导入头像昵称失败了,错误码:'.$res['ErrorCode'].' 错误描述:'.$res['ErrorInfo']);
                        return $this->fail();
                    }
                    DB::commit();
                    return $this->success($model);
                }else{
                    DB::rollback();
                    return $this->fail();
                }
            }else{
                DB::rollback();
                return $this->fail();
            }
        }catch (\Exception $e){
            Log::info('审核通过出错了：'.$e->getMessage());
            DB::rollback();
            return $this->fail();
        }
    }

    /**
     *操作日志
     * @return MemberController
     */
    public function checkLog(){
        $id     = Input::get('id',0);
        if(!$id){
            return $this->fail(100001);
        }
        $data = MemberInfoChecked::where('mid',$id)->first();
        if(!$data){
            return $this->success();
        }
        $list = MemberInfoCheckedLog::from('members_info_check_log as a')
            ->leftjoin('members as b','a.mid','=','b.id')
            ->leftjoin('members_info_checked as c','a.mid','=','c.mid')
            ->leftjoin('user as d','a.uid','=','d.id')
            ->where('a.mid',$data->mid)
            ->orderBy('a.id','desc')
            ->get(['a.*','b.nick_name','c.name','d.name as admin_name']);
        return $this->success($list);
    }
}
