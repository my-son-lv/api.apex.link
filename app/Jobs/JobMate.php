<?php

namespace App\Jobs;

use App\Models\Companys;
use App\Models\Country;
use App\Models\Job;
use App\Models\MemberInfoChecked;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class JobMate implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;


    /**
     * 任务最大尝试次数。
     *
     * @var int
     */
    public $tries = 1;
    /**
     * 任务运行的超时时间。
     *
     * @var int
     */
//    public $timeout = 300;

    protected  $data;

    /**
     * JobMate constructor.
     * @param $data
     */
    public function __construct($data)
    {
        //mid 外教id
        //jid 企业id
        //type 1外教 修改 新增 2职位 新增 修改
        $this->data = $data;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $data  = $this->data;
        $type  = $data['type'];
        if($type == 1){
            //查询当前外教
            $member_check_info = MemberInfoChecked::where('mid',$data['mid'])->first();
            //查询所有 职位 匹配当前外教
            $isEnd = true;
            $forNum = 0;
            try {
                DB::beginTransaction();
                while ($isEnd){
                    $job = Job::offset($forNum)->limit(1)->first();
                    ++$forNum;
                    $level = 0;
                    if(!$job)
                    {
                        $isEnd = false;
                        continue;
                    }
                    //计算职位分数 并出入数据库
                    ($job->work_type && $member_check_info->job_work_type == $job->work_type) && ++$level;
                    //类型 线上 线下
                    ($job->type!==null && $member_check_info->job_type == $job->type)  && ++$level;
                    //薪资-- 取消暂时
                    //学校类型
                    $arr = [1 => 1, 2 => 5, 3 => 3, 4 => 2, 5 => 4, 6 => 6 ];
                    $schoole = array_filter(explode(',',$member_check_info->school_type));
                    $arr1 = [];
                    if($schoole){ foreach ($schoole as $k => $v){ array_push($arr1,$arr[$v]); }}
                    $company = Companys::find($job->cid);
                    if($company){
                        if(array_intersect(explode(',',$company->type),$arr1)){
                            ++$level;
                        }
                    }else{
                        continue;
                    }
                    //学生年龄
                    if($job->job_year !==null && $member_check_info->student_age!==null && $job->job_year == $member_check_info->student_age){
                        ++$level;
                    }
                    //工作年限
                    if($job->job_year!==null){
                        switch ($job->job_year){
                            case 1:
                                $member_check_info->working_seniority == 1 && ++$level;break;
                            case 2:
                                in_array($member_check_info->working_seniority,[2,3,4]) && ++$level;break;
                            case 3:
                                in_array($member_check_info->working_seniority,[5,6]) && ++$level;break;
                            case 4:
                                in_array($member_check_info->working_seniority,[6,7,8,9,10]) && ++$level;break;
                            case 5:
                                in_array($member_check_info->working_seniority,[11]) && ++$level;break;
                        }
                    }
                    //工作城市
                    if($member_check_info->all_city!==null){
                        if($member_check_info->all_city == 1){
                            ++$level;
                        }else{
                            if(in_array($job->job_city,explode(',',$member_check_info->working_city))){
                                ++$level;
                            }
                        }
                    }
                    //学历要求
                    if($job->edu_type!==null){
                        if($job->edu_type == 4) {
                            ++$level;
                        }else{
                            if($member_check_info->university > 3 && ($member_check_info->university - 2) == $job->edu_type){
                                ++$level;
                            }
                        }
                    }
                    //国籍
                    if ($job->first_language) {
                        if($member_check_info->nationality && $member_check_info->nationality > 0){
                            $country = Country::find($member_check_info->nationality);
                            if($country){
                                //都是母语+1
                                if($country->flg == 1 && $job->first_language ==1){
                                    ++$level;
                                }
                                //都是非母语+1
                                if($country->flg == 0 && $job->first_language ==2){
                                    ++$level;
                                }
                            }
                        }
                    } else {
                        //不限直接+1
                        ++$level;
                    }
                    //性别
                    $member_check_info->sex == $job->sex  && ++$level;
                    //教学证书
                    ($member_check_info->celta_flg  == 2 || $member_check_info->cert_other_flg == 2 ||  $member_check_info->edu_cert_flg && $member_check_info->edu_auth_flg) && ++$level;
                    $model = \App\Models\JobMate::where(['jid' => $job->id ,'mid' => $member_check_info->mid ])->first();
                    if($model){
                        $model->update(['level' => $level] );
                    }else{
                        \App\Models\JobMate::create(['jid' => $job->id ,'mid' => $member_check_info->mid , 'level' => $level]);
                    }
                }
                DB::commit();
            }catch (\Exception $e){
                Log::error(json_encode($e->getTrace()));
                Log::error(json_encode($e->getLine()));
                Log::error(json_encode($e->getMessage()));
                DB::rollback();
            }
        }else if($type == 2){
            $job = Job::find($data['jid']);
            //查询所有 职位 匹配当前外教
            $isEnd = true;
            $forNum = 0;
            try {
                DB::beginTransaction();
                while ($isEnd){
                    $member_check_info = MemberInfoChecked::offset($forNum)->limit(1)->first();
                    ++$forNum;
                    $level = 0;
                    if(!$member_check_info)
                    {
                        $isEnd = false;
                        continue;
                    }
                    //计算职位分数 并出入数据库
                    ($job->work_type && $member_check_info->job_work_type == $job->work_type) && ++$level;
                    //类型 线上 线下
                    ($job->type!==null && $member_check_info->job_type == $job->type)  && ++$level;
                    //薪资-- 取消暂时
                    //学校类型
                    $arr = [1 => 1, 2 => 5, 3 => 3, 4 => 2, 5 => 4, 6 => 6 ];
                    $schoole = array_filter(explode(',',$member_check_info->school_type));
                    $arr1 = [];
                    if($schoole){ foreach ($schoole as $k => $v){ array_push($arr1,$arr[$v]); }}
                    $company = Companys::find($job->cid);
                    if($company){
                        if(array_intersect(explode(',',$company->type),$arr1)){
                            ++$level;
                        }
                    }else{
                        continue;
                    }
                    //学生年龄
                    if($job->job_year !==null && $member_check_info->student_age!==null && $job->job_year == $member_check_info->student_age){
                        ++$level;
                    }
                    //工作年限
                    if($job->job_year!==null){
                        switch ($job->job_year){
                            case 1:
                                $member_check_info->working_seniority == 1 && ++$level;break;
                            case 2:
                                in_array($member_check_info->working_seniority,[2,3,4]) && ++$level;break;
                            case 3:
                                in_array($member_check_info->working_seniority,[5,6]) && ++$level;break;
                            case 4:
                                in_array($member_check_info->working_seniority,[6,7,8,9,10]) && ++$level;break;
                            case 5:
                                in_array($member_check_info->working_seniority,[11]) && ++$level;break;
                        }
                    }
                    //工作城市
                    if($member_check_info->all_city!==null){
                        if($member_check_info->all_city == 1){
                            ++$level;
                        }else{
                            if(in_array($job->job_city,explode(',',$member_check_info->working_city))){
                                ++$level;
                            }
                        }
                    }
                    //学历要求
                    if($job->edu_type!==null){
                        if($job->edu_type == 4) {
                            ++$level;
                        }else{
                            if($member_check_info->university > 3 && ($member_check_info->university - 2) == $job->edu_type){
                                ++$level;
                            }
                        }
                    }
                    //国籍
                    if ($job->first_language) {
                        if($member_check_info->nationality && $member_check_info->nationality > 0){
                            $country = Country::find($member_check_info->nationality);
                            if($country){
                                //都是母语+1
                                if($country->flg == 1 && $job->first_language ==1){
                                    ++$level;
                                }
                                //都是非母语+1
                                if($country->flg == 0 && $job->first_language ==2){
                                    ++$level;
                                }
                            }
                        }
                    } else {
                        //不限直接+1
                        ++$level;
                    }
                    //性别
                    $member_check_info->sex == $job->sex  && ++$level;
                    //教学证书
                    ($member_check_info->celta_flg  == 2 || $member_check_info->cert_other_flg == 2 ||  $member_check_info->edu_cert_flg && $member_check_info->edu_auth_flg) && ++$level;
                    $model = \App\Models\JobMate::where(['jid' => $job->id ,'mid' => $member_check_info->mid ])->first();
                    if($model){
                        $model->update(['level' => $level] );
                    }else{
                        \App\Models\JobMate::create(['jid' => $job->id ,'mid' => $member_check_info->mid , 'level' => $level]);
                    }
                }
                DB::commit();
            }catch (\Exception $e){
                Log::error(json_encode($e->getTrace()));
                Log::error(json_encode($e->getLine()));
                Log::error(json_encode($e->getMessage()));
                DB::rollback();
            }

        }

    }
}
