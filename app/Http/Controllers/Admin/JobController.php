<?php

namespace App\Http\Controllers\Admin;

use App\Jobs\JobMate;
use App\Models\CompanyAdvier;
use App\Models\Companys;
use App\Models\Interview;
use App\Models\Job;
use App\Models\JobApplication;
use App\Models\Notice;
use App\Models\Region;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Image;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class JobController extends Controller
{
    #获取所有开启状态的职位
    public function getOpenJobList(Request $request){
        $list = Job::with(['company' => function($q){
            $q->select(['id','company_name','logo']);
        }])->where('status',1)->get(['id','cid','name','job_type','job_city','pay','pay_unit','status']);
        foreach ($list as $k => $v){
            $v->name = $v->name.'('.$v->company->company_name.')';
        }
        return $this->success($list);
    }

    /**
     * 生成推荐海报
     *
     * @param Request $request
     */
    public function makeRecommPlaybill(Request $request){
        $job = Job::find($request->id);
        $img = Image::make(public_path().'/haibao.png');
        $path = public_path('/tmp/'.date(("YmdHis").rand(1000,9999999).'png'));
        QrCode::format('png')->size(200)->margin(0)->generate(config('app.m_teach_url').'/#/jobsDetails?id='.$job->id.'&cid='.$job->cid,$path);
        $img->insert(file_get_contents($path), 'right-bottom', 70,111);
        unlink($path);
        $img->text(str_limit($job->name,24,'...'), 80, 1040, function($font) {
            $font->file(public_path().'/PingFang-SC-Medium.otf');
            $font->size(28);
            $font->color('#000');
        });
        $pay = explode(',',$job->pay);
        $img->text('￥'.$pay[0].'-'.$pay[1], 80, 1090, function($font) {
            $font->file(public_path().'/PingFang-SC-Medium.otf');
            $font->size(28);
            $font->color('#FF6010');
        });
        return $img->response("png");
    }

    public function index(Request $request){
        $id       = $request->get('id');
        $page   = $request->get('page',1);
        $pageSize = $request->get('pageSize',config('admin.pageSize'));
        if($page<1) $page = 1;
        $list = Job::from('job')
            ->leftjoin('companys as b','job.cid','=','b.id');
        if($id){
            $list = $list->where('job.cid',$id);
        }
        $count = ceil($list->count()/$pageSize);
        $list = $list->orderBy('job.sort','desc')->orderBy('updated_at','desc')->offset(($page-1)*$pageSize)->limit($pageSize)->get(['job.*','b.company_name']);
        foreach ($list as $k => $v){
            $advert = CompanyAdvier::where('cid',$v->cid)->orderBy('id','desc')->first();
            if($advert){
                $user = User::find($advert->uid);
            }
            $v->user_name = $advert && $user ? $user->name : '';
            if($v->job_city){
                $v->job_area_data = Region::find($v->job_city);
                $v->job_city_data = Region::find($v->job_area_data->pid);
                $v->job_province_data  = Region::find($v->job_city_data->pid);
                $v->job_app = JobApplication::where('jid',$v->id)->count();
            }else{
                $v->job_area_data =  null;
                $v->job_city_data  = null;
                $v->job_province_data  = null;
            }
        }
        return $this->success(['count' => $count,'list' => $list,'page' => $page]);
    }

    public function add(Request $request){
        //name      职位名称
        //job_city  期望工作地
        //type      1线下 2线上
        //work_type 1不限 2全职 3兼职
        //language  语言 1英语
        //student_age 学生年龄  1 0-3 2 3-6 3 7-12 4 13-18 5 18以上
        //job_day_time 一周工作几个小时 1-40之间 1,40
        //pay       金额 区间例：15,20 英文逗号分隔 单位K
        //num       招聘人数
        //benefits  福利待遇
        //job_info  工作介绍
        //first_language '母语 0不限 1母语 2非母语
        //colour    肤色 0不限 1白色
        //sex       0不限 1男 2女
        //edu_type  学历 1 本科及以上 2 研究生及以上 3 博士及以上 4 不限
        //cert      证书  英文逗号分隔 1 TEFL 2 TESOL 3 TESL 4 CELTA
        //job_year  教学经验 1 1年以内； 2 1-3年；3 3-5年；4 5-10年；5 10年以上
        //visa_ask  签证要求 1工签 2其他
        //start_time    招聘开始时间
        //end_time      招聘结束时间
        //memo          备注
        $user = $request->user;
        unset($request->user);
        unset($request->token);
        $data = $request->all();
        $data['flg'] = 2;
        $company = Companys::find($data['cid']);
        $model = Job::create($data);
        Notice::addNotice(returnNoticeMsg(['user' => $user->name,'company_name' => $company->company_name],2005),2,2005);
        $this->dispatch(new \App\Jobs\JobMate(['jid' =>$model->id , 'type' => 2 ]));
        return $this->success($model);
    }

    public function edit(Request $request){
        $user = $request->user;
        unset($request->user);
        unset($request->token);
        $id = $request->get('id',0);
        $data = $request->all();
        unset($data['id']);
        $job = Job::find($id);
        $job->update($data);
        $company = Companys::find($data['cid']);
        Notice::addNotice(returnNoticeMsg(['user' => $user->name,'company_name' => $company->company_name],2006),2,2006);
        dispatch(new JobMate(['jid' =>$job->id , 'type' => 2 ]));
        return $this->success($job);
    }

    public function delete(Request $request){
        try {
            DB::beginTransaction();
            $flg = Job::where('id',$request->id)->delete();
            //删除面试记录
            $flg1 = Interview::where('jid',$request->id)->delete();
            if($flg!==false && $flg1!==false){
                DB::commit();
                return $this->success();
            }else{
                DB::rollback();
                return $this->fail();
            }
        }catch (\Exception $e){
            DB::rollback();
            Log::info('删除职位失败');
            return $this->fail();
        }
    }

    public function sort(Request $request){
        $flg = Job::where('id',$request->id)->update(['sort' => $request->sort]);
        if($flg!==false){
            return $this->success();
        }else{
            return $this->fail();
        }
    }


    /**
     * 职位列表
     * @param Request $request
     * @return \App\Http\Controllers\Index\JobController
     */
    public function jobList(Request $request){
        $id       = $request->get('id');
        $page   = $request->get('page',1);
        $pageSize = $request->get('pageSize',config('admin.pageSize'));
        if($page<1) $page = 1;
        $list = Job::where('cid',$id);
        $count = ceil($list->count()/$pageSize);
        $list = $list->orderBy('sort','desc')->orderBy('id','desc')->offset(($page-1)*$pageSize)->limit($pageSize)->get();
        foreach ($list as $k => $v){
            $v->done = 1;
        }
        return $this->success(['count' => $count,'list' => $list,'page' => $page]);

    }

    /**
     * 职位详情
     * @param Request $request
     * @return \App\Http\Controllers\Index\JobController
     */
    public function jobDesc(Request $request){
        $id = $request->get('id','');
        if(!$id){
            return $this->fail(100001);
        }
        $job = Job::find($id);
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
            return $this->success($job);
        }
    }

    /**
     * 关闭职位
     * @param Request $request
     * @return JobController
     */
    public function jobClose(Request $request){
        $id     = $request->get('id','');
        $type   = $request->get('type',1);
        if(!$id){
            return $this->fail(100001);
        }
        $job = Job::find($id);
        if(!$job){
            return $this->fail(2000201);
        }else{
            $job->status = $type;
            if($job->save()){
                return $this->success();
            }else{
                return $this->fail();
            }

        }
    }
}
