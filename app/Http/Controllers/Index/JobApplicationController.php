<?php

namespace App\Http\Controllers\Index;

use App\Jobs\SendEmail;
use App\Models\Companys;
use App\Models\Country;
use App\Models\Down;
use App\Models\Files;
use App\Models\Job;
use App\Models\JobApplication;
use App\Models\Member;
use App\Models\MemberInfo;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class JobApplicationController extends Controller
{
    public function jobResult(Request $request){//1未处理  2可以聊 3不合适
        $flg = JobApplication::where('id',$request->id)->where('result',1)->update(['result' => $request->result]);
        if($flg){
            if($request->result == 3){
                $jobApp = JobApplication::find($request->id);
                $member = Member::find($jobApp->mid);
                $job = Job::find($jobApp->jid);
                $memberInfo = MemberInfo::where('mid',$member->id)->first();
                $this->dispatch(new SendEmail([
                    'email'     => $member->email,
                    'template'  => 'jobResult',
                    'title'     => 'APEX GLOBAL - Job Application Update',
                    'job_name'  => $job->name,
                    'company_name' => $request->company->company_name,
                    'teach_name' => $memberInfo->last_name,
                ]));
            }
            return $this->success();
        }else{
            return  $this->fail(2000201);
        }

    }

    #已读
    public function JobAppRead(Request $request){
        $read_flg = JobApplication::where('cid', $request->company->id)->where('read_flg',1)->count();
        if($read_flg){
            JobApplication::where('cid', $request->company->id)->update(['read_flg' => 2]);
        }
        return $this->success();
    }

    //
    #职位申请列表
    public function jobAppList(Request $request)
    {
        $type = $request->get('type', '');
        $page = $request->get('page', 1);
        $pageSize = $request->get('pageSize', 15);
        $list = JobApplication::from('job_applications AS a')
            ->join('members_info AS b', 'b.mid', '=', 'a.mid')
            ->join('job AS c', 'c.id', '=', 'a.jid')
            ->where('a.cid', $request->company->id);
        if ($type) {
            $list = $list->where('a.jid', $type);
        }
        $list = $list->orderBy('a.id', 'desc');
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
                'b.pay_type',
                'a.created_at',
                'b.photos',
                'b.desc',
                'b.university',
                'c.name as job_name',
                'b.category',
                'b.nationality',
                'b.sex',
                'a.read_flg',
                'a.id as job_app_id',
                'a.result',
            ]);
        $read_flg = JobApplication::where('cid', $request->company->id)->where('read_flg',1)->count();
        foreach ($list as $k => $v) {//11
            if ($v->photos) {
                $v->photos_path = Files::whereIn('id', explode(',', $v->photos))->get();
            }
            $country = Country::find($v->nationality);
            $v->nationality_val = $country['code'];
        }
        return $this->success(['count' => $count, 'total' => $total, 'list' => $list , 'read_flg' => $read_flg]);
    }
}
