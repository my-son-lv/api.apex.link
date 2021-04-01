<?php

namespace App\Http\Controllers\Index;

use App\Models\Companys;
use App\Models\Files;
use App\Models\Jiping;
use App\Models\Job;
use App\Models\JobApplication;
use App\Models\JobCollect;
use App\Models\Member;
use App\Models\MemberInfoChecked;
use App\Models\Notice;
use App\Models\Region;
use App\Models\Tuisong;
use App\Models\Vip;
use App\Models\VipAction;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class JobController extends Controller
{

    #精准推送
    public function jingzhuntuisong(Request $request){
        if(!$request->id){
            return $this->fail(100001);
        }
        $job = Job::find($request->id);
        //判断是否是会员
        if(!$request->company->vip_actions_id){
            return $this->fail(2500003);
        }
        //判断次数是否够用
        $vipAction = VipAction::find($request->company->vip_actions_id);
        $vip = Vip::find($vipAction->vip_id);
        if($vipAction->yy_tuisong >= $vip->tuisong){
            //否 发送飞书通知
            if (config('app.env') == 'production') {
                //获取运营部通知手机好
                $phones = $this->getYunYingUserPhone();
                //获取通知内容
                $Feishu['company_name'] = $request->company->company_name;
                $this->FeiShuSendText($phones, returnFeiShuMsg(14, $Feishu));
            }
            return $this->fail(2500002);
        }
        DB::beginTransaction();
        try {
            //是 记录 减少次数
            Tuisong::create([
                'cid'   => $request->company->id,
                'jid'   => $request->id,
                'vip_id'=> $request->company->vip_actions_id,
            ]);
            $vipAction->yy_tuisong = $vipAction->yy_tuisong+1;
            $vipAction->save();
            DB::commit();
            if (config('app.env') == 'production') {
                //获取运营部通知手机好
                $phones = $this->getYunYingUserPhone();
                //获取通知内容
                $Feishu['company_name'] = $request->company->company_name;
                $Feishu['job_name'] = $job->name;
                $this->FeiShuSendText($phones, returnFeiShuMsg(16, $Feishu));
            }
            return  $this->success();
        }catch (\Exception $e){
            DB::rollBack();
            return $this->fail();
        }
    }

    #急聘服务
    public function jiping(Request $request){
        if(!$request->id){
            return $this->fail(100001);
        }
        $job = Job::find($request->id);
        //判断是否是会员
        if(!$request->company->vip_actions_id){
            return $this->fail(2500003);
        }
        //判断次数是否够用
        $vipAction = VipAction::find($request->company->vip_actions_id);
        $vip = Vip::find($vipAction->vip_id);
        if($vipAction->yy_jiping >= $vip->jiping){
            //否 发送飞书通知
            if (config('app.env') == 'production') {
                //获取运营部通知手机好
                $phones = $this->getYunYingUserPhone();
                //获取通知内容
                $Feishu['company_name'] = $request->company->company_name;
                $this->FeiShuSendText($phones, returnFeiShuMsg(15, $Feishu));
            }
            return $this->fail(2500002);
        }
        DB::beginTransaction();
        try {
            //是 记录 减少次数
            Jiping::create([
                'cid'   => $request->company->id,
                'jid'   => $request->id,
                'vip_id'=> $request->company->vip_actions_id,
            ]);
            $vipAction->yy_jiping = $vipAction->yy_jiping+1;
            $vipAction->save();
            DB::commit();
            if (config('app.env') == 'production') {
                //获取运营部通知手机好
                $phones = $this->getYunYingUserPhone();
                //获取通知内容
                $Feishu['company_name'] = $request->company->company_name;
                $Feishu['job_name'] = $job->name;
                $this->FeiShuSendText($phones, returnFeiShuMsg(17, $Feishu));
            }
            return  $this->success();
        }catch (\Exception $e){
            DB::rollBack();
            return $this->fail();
        }
    }

    public function top(Request $request){
        $id = $request->get('id','');
        $type = $request->get('type',1);//1置顶 2取消置顶
        if(!$id){
            return $this->fail(1000001);
        }
        $job = Job::where('cid',$request->company->id)->where('id',$id)->first();
        if(!$job){
            return $this->fail(2000201);
        }
        if($type == 1){
            if($job->top== 1){
                return  $this->fail(2000217);
            }
            //查询置顶数
            $top_num = Job::where('cid',$request->company->id)->where('top',1)->count();
            //查询是否是会员
            if(!$request->company->vip_actions_id){
                //否
                return $this->fail(2000216);
            }else{
                $action = VipAction::find($request->company->vip_actions_id);
                $vip = Vip::find($action->vip_id);
                if($top_num >= $vip->top){
                    return $this->fail(2000215);
                }
            }

            DB::beginTransaction();
            try{
                //置顶职位
                $flg = Job::where('id',$id)->update(['top' => 1, 'top_exp_time' => date('Y-m-d H:i:s',strtotime("+7 day", time()))]);
                //置顶数加1
                $flg1 = VipAction::where('id',$request->company->vip_actions_id)->increment('yy_top');
                if($flg!==false && $flg1!==false){
                    DB::commit();
                    return $this->success();
                }else{
                    DB::rollBack();
                    return $this->fail();
                }
            }catch (\Exception $e) {
                Log::info('置顶失败：'.$e->getMessage());
                DB::rollBack();
                return $this->fail();
            }
        }else{
            if($job->top== 0){
                return  $this->fail(2000218);
            }
            //取消置顶职位
            $flg = Job::where('id',$id)->update(['top' => 0, 'top_exp_time' => null]);
            if($flg!==false){
                return $this->success();
            }else{
                return $this->fail();
            }
        }
    }

    #获取所有职位
    public function getJobList(Request $request){
        return $this->success(Job::where('cid',$request->company->id)->get(['id','cid','name']));
    }

    #获取指定职位
    public function topList(Request $request)
    {
        $token  = $request->get('token','');
        $member = null;
        $member = null;
        if($token){
            $member = Member::where('token',$token)->first();
//            $memberCheck = MemberInfoChecked::where('mid',$member->id)->first();
        }
        $list = Job::where('top',1)->where('status',1)->orderBy('id','desc')->get();
        /*if($member && $memberCheck && ($memberCheck->status==1 || $memberCheck->status==2)){
            $list = Job::from('job as a')
                ->join('job_mates as b','a.id','=','b.jid')
                ->where('a.status',1)
                ->where('a.top',1)
                ->where('b.mid',$member->id);
            $list = $list->where(function ($query) use ( $request ){
                $request->name && $query->Orwhere('a.name','like',"%{$request->name}%");//名字
                $request->city && $query->OrwhereIn('a.job_city',explode(',',$request->city));
                $request->type && $query->OrWhere('a.work_type',$request->type);//1不限 2全职 3兼职
                $request->cate && $query->OrWhere('a.type',$request->cate);//1线上 2线下
                $request->salary && $query->OrWhere('a.pay',$request->salary);//金额
            });
            $list = $list->orderBy('a.id','desc')->orderBy('b.level','desc');
                ->get(['a.*','b.mid','b.id','b.jid','b.level','a.id as id']);
        }else{
            $list = Job::where('status',1);
            $list = $list->where(function ($query) use ( $request ){
                $request->name && $query->Orwhere('name','like',"%{$request->name}%");//名字
                $request->city && $query->OrwhereIn('job_city',explode(',',$request->city));
                $request->type && $query->OrWhere('work_type',$request->type);//1不限 2全职 3兼职
                $request->cate && $query->OrWhere('type',$request->cate);//1线上 2线下
                $request->salary && $query->OrWhere('pay',$request->salary);//金额
            });
            $list = $list->orderBy('sort','desc')->orderBy('id','desc')->get();
        }*/
        foreach ($list as $k => $v){
            $v->done = 1;
            if($v->job_city){
                $v->job_area_data = Region::find($v->job_city);
                $v->job_city_data = Region::find($v->job_area_data->pid);
                $v->job_province_data  = Region::find($v->job_city_data->pid);
            }else{
                $v->job_area_data =  [];
                $v->job_city_data  = [];
                $v->job_province_data  = [];
            }
            $v->collect_type = 0;//未收藏
            if($member){
                $v->collect_type = JobCollect::where('mid',$member->id)->where('jid',$v->id)->count() ? 1 : 0;
            }
        }
        return $this->success($list);
    }

    //
    public function allList(Request $request){
        $page = $request->get('page',1);
        $pageSize = $request->get('pageSize',15);
        $token  = $request->get('token','');
        $sort_type   = $request->get('sort_type',1);


        $member = null;
        $citys = explode(',',$request->city);
        $citys_all = Region::whereIn('pid',$citys)->pluck('id');
        if($token){
            $member = Member::where('token',$token)->first();
            $memberCheck = MemberInfoChecked::where('mid',$member->id)->first();
        }
        if($page<1) $page = 1;
        if($member && $memberCheck && ($memberCheck->status==1 || $memberCheck->status==2)){
            $list = Job::from('job as a')
                ->join('job_mates as b','a.id','=','b.jid')
                ->where('a.status',1)
                ->where('b.mid',$member->id);
            $list = $list->where(function ($query) use ( $request ,$citys_all){
                $request->name && $query->Orwhere('a.name','like',"%{$request->name}%");//名字
                $request->city && $query->OrwhereIn('a.job_city',$citys_all);
                $request->type && $query->OrWhere('a.work_type',$request->type);//1不限 2全职 3兼职
                $request->cate && $query->OrWhere('a.type',$request->cate);//1线上 2线下
                $request->salary && $query->OrWhere('a.pay',$request->salary);//金额
            });
            $count = ceil($list->count()/$pageSize);
            if($sort_type == 1){
                $list = $list->orderBy('b.level','desc');
            }
            $list = $list->orderBy('a.id','desc')
                ->offset(($page-1)*$pageSize)
                ->limit($pageSize)
                ->get(['a.*','b.mid','b.id','b.jid','b.level','a.id as id']);
        }else{
            $list = Job::where('status',1);
            $list = $list->where(function ($query) use ( $request,$citys_all ){
                $request->name && $query->Orwhere('name','like',"%{$request->name}%");//名字
                $request->city && $query->OrwhereIn('job_city',$citys_all);
                $request->type && $query->OrWhere('work_type',$request->type);//1不限 2全职 3兼职
                $request->cate && $query->OrWhere('type',$request->cate);//1线上 2线下
                $request->salary && $query->OrWhere('pay',$request->salary);//金额
            });
            $count = ceil($list->count()/$pageSize);
            if($sort_type == 1){
                $list = $list->orderBy('sort','desc');
            }
            $list = $list->orderBy('updated_at','desc')->offset(($page-1)*$pageSize)->limit($pageSize)->get();
        }
        foreach ($list as $k => $v){
            $v->done = 1;
            if($v->job_city){
                $v->job_area_data = Region::find($v->job_city);
                $v->job_city_data = Region::find($v->job_area_data->pid);
                $v->job_province_data  = Region::find($v->job_city_data->pid);
            }else{
                $v->job_area_data =  [];
                $v->job_city_data  = [];
                $v->job_province_data  = [];
            }
            $v->collect_type = 0;//未收藏
            if($member){
                $v->collect_type = JobCollect::where('mid',$member->id)->where('jid',$v->id)->count() ? 1 : 0;
            }
        }
        return $this->success(['count' => $count,'list' => $list,'page' => $page]);
    }

    /**
     * 职位详情
     * @param Request $request
     * @return JobController
     */
    public function jobDesc(Request $request){
        $id = $request->get('id','');
        $token = $request->get('token','');

        if(!$id){
            return $this->fail(100001);
        }
        $job = Job::find($id);
        $click = $job['click_count']+1;
        Job::where('id',$id)->update(['click_count'=>$click]);
        if(!$job){
            return $this->fail(2000201);
        }else{
            if($job->job_city){
                $job->job_area_data = Region::find($job->job_city);
                $job->job_city_data = Region::find($job->job_area_data->pid);
                $job->job_province_data  = Region::find($job->job_city_data->pid);
            }else{
                $job->job_area_data =  [];
                $job->job_city_data  = [];
                $job->job_province_data  = [];
            }
            //完成数
            $job->done = rand(0,$job->num);
            $job->collect_type = 0;//未收藏
            $member = null;
            if($token){
                $member = Member::where('token',$token)->first();
                if($member){
                    $job->collect_type = JobCollect::where('mid',$member->id)->where('jid',$job->id)->count() ? 1 : 0;
                }
            }
            //查询学校详情
            $company = Companys::find($job->cid);
            $data = [
                'logo'  => '',
                'type'  => $company->type,
                'school_img_1'  => '',
                'school_img_2'  => '',
                'company_name'  => $company->company_name,
                'school_en_info'  => $company->school_en_info,
                'vip'           => $company->vip_actions_id ? 1 : 0
            ];
            if($company->logo){
                $data['logo'] = Files::find($company->logo);
            }
            if($company->school_img_1){
                $data['school_img_1'] = Files::find($company->school_img_1);
            }
            if($company->school_img_2){
                $data['school_img_2'] = Files::find($company->school_img_2);
            }
            $job->company = $data;
            return $this->success($job);
        }
    }

    /**
     * 更新职位
     * @param Request $request
     * @return JobController
     */
    public function jobUpdate(Request $request){
        $data = $request->all();
        $id = $data['id'];
        unset($data['id']);
        unset($data['token']);
        unset($data['company']);
        if(!$id){
            return $this->fail(100001);
        }
        if(count($data)<1){
            return $this->fail(100001);
        }
        $data['updated_at'] = date("Y-m-d H:i:s");
        $flg = Job::where('id',$id)->update($data);
        if($flg!==false){
            $this->dispatch(new \App\Jobs\JobMate(['jid' =>$id , 'type' => 2 ]));
            return $this->success();
        }else{
            return $this->fail();
        }
    }

    /**
     * 关闭职位 1开启 2关闭
     * @param Request $request
     * @return JobController
     */
    public function jobClose(Request $request){
        $id     = $request->get('id',0);
        $type   = $request->get('type',1);
        if(!$id){
            return $this->fail(100001);
        }
        $job = Job::find($id);
        if($type == 1  && $job->flg == 1){
            $up_job_num = Job::where('cid',$request->company->id)->where('status',1)->where('flg',1)->count();
            //判断当前是否是会员
            /*if($request->company->vip_actions_id){
                $action = VipAction::find($request->company->vip_actions_id);
                $vip    = Vip::find($action->vip_id);
                if($up_job_num >= ($vip->job_num + 1)){
                    return $this->fail(2000219);
                }
            }else{
                if($up_job_num>=1){
                    return $this->fail(2000219);
                }
            }*/
            if(!$request->company->vip_actions_id){
                if($up_job_num>=1){
                    return $this->fail(2000219);
                }
            }
        }
        $type = $type == 1 ? 1: 2;
        $flg = Job::where('id',$id)->update(['status' => $type,'updated_at' => date("Y-m-d H:i:s")]);
        if($flg){
            //修改vipaction已用职位数
            if($request->company->vip_actions_id && $type == 1 && $request->company->flg == 1){
                VipAction::where('id',$request->company->vip_actions_id)->increment('yy_job_num');
            }
            return $this->success();
        }else{
            return $this->fail();
        }
    }

    /**
     * 删除职位
     * @param Request $request
     * @return JobController
     */
    public function jobDel(Request $request){
        $id     = $request->get('id',0);
        if(!$id){
            return $this->fail(100001);
        }
        $job = Job::find($id);
        if($request->company->vip_actions_id && $job->flg == 1){
            VipAction::where('id',$request->company->vip_actions_id)->decrement('yy_job_num');
        }
        $flg = Job::where('id',$id)->delete();
        if($flg){
            return $this->success();
        }else{
            return $this->fail();
        }
    }

    /**
     * 职位列表
     * @param Request $request
     * @return JobController
     */
    public function jobList(Request $request){
        $token  = $request->get('token');
        $page   = $request->get('page',1);
        $pageSize = $request->get('pageSize',config('admin.pageSize'));
        if($page<1) $page = 1;
        $company = Companys::where('token',$token)->first();
        $list = Job::where('cid',$company->id);
        $count = ceil($list->count()/$pageSize);
        $list = $list->orderBy('id','desc')->offset(($page-1)*$pageSize)->limit($pageSize)->get();
        foreach ($list as $k => $v){
            if($v->job_city){
                $v->job_area_data = Region::find($v->job_city);
                $v->job_city_data = Region::find($v->job_area_data->pid);
                $v->job_province_data  = Region::find($v->job_city_data->pid);
            }else{
                $v->job_area_data =  [];
                $v->job_city_data  = [];
                $v->job_province_data  = [];
            }
            //完成数
            $v->done = rand(0,$v->num);
            $v->job_app_num = JobApplication::where('jid',$v->id)->where('cid',$company->id)->count();
        }
        return $this->success(['count' => $count,'list' => $list,'page' => $page]);

    }

    /**
     * 添加职位
     * @param Request $request
     * @return JobController
     */
    public function addJob(Request $request){
        $data = $request->all();
        $token = $data['token'];
        unset($data['company']);
        unset($data['token']);
        $company = Companys::where('token',$token)->first();
        //查询发布职位数
        $count = Job::where('cid',$company->id)->where('flg',1)->count();
        //判断企业状态 未入住不可添加职位
        if($company->status != 2){
            return $this->fail('请完善企业信息，24小时审核通过后即可发布职位');
        }

        //判断是否是会员
        if(!$company->vip_actions_id){//不是会员
            if($count >= 1){
                return $this->fail(2000219);
            }
        }
        /*else{
            $action = VipAction::find($company->vip_actions_id);
            $vip    = Vip::find($action->vip_id);
            if($count >= ($vip->job_num + 1)){
                return $this->fail(2000219);
            }
        }*/
        if(isset($data['job_city'])){
            if($data['job_city']==100000){
                unset($data['job_city']);
            }
        }
        $data['cid'] = $company->id;
        $data['created_at'] = $data['updated_at'] = date("Y-m-d H:i:s");
        $model = Job::create($data);
        if($model){
            //修改vipaction已用职位数
//            if($company->vip_actions_id){
//                VipAction::where('id',$company->vip_actions_id)->update(['yy_job_num' => $count+1]);
//            }
            Notice::addNotice(returnNoticeMsg(['company_name' => $company->company_name],2002),2,2002);
            $this->dispatch(new \App\Jobs\JobMate(['jid' =>$model->id , 'type' => 2 ]));
            if(config('app.env') == 'production') {
                //获取运营部通知手机好
                $phones = $this->getYunYingUserPhone();
                //获取通知内容
                $Feishu['company_name'] = $company->company_name;
                $Feishu['time'] = date("Y年m月d日 H:i");
                $this->FeiShuSendText($phones,returnFeiShuMsg(3 ,$Feishu));
            }
            return $this->success();
        }else{
            return $this->fail();
        }

    }

}
