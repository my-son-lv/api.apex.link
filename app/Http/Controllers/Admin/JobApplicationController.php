<?php

namespace App\Http\Controllers\Admin;

use App\Jobs\SendWxNotice;
use App\Models\Companys;
use App\Models\Job;
use App\Models\JobApplication;
use App\Models\MemberInfo;
use App\Models\Official;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class JobApplicationController extends Controller
{

    #管理后台 招聘需求 投递 列表
    public function jobAppList(Request $request)
    {
        if (!$request->id) return $this->fail(2000001);
        $list = JobApplication::with(['member_info','member_info.nationality_val'])->where('jid', $request->id)
            ->whereHas('member_info', function ($q) {
                    $q->select(['mid','name', 'last_name', 'nationality', 'sex','university']);
                }
            )
            ->orderBy('id', 'desc')
            ->paginate($request->pageSize ?? $request->pageSize);
        return $list;
    }

    //
    #投递给公司
    public function jobApplication(Request $request)
    {
        $jid = $request->get('jid', 0);
        $mid = $request->get('mid', 0);
        if (!$jid || !$mid) {
            return $this->fail(2000001);
        }
        $jid_arr = explode(',', $jid);
        foreach ($jid_arr as $k => $v) {
            $job = Job::find($v);
            if ($job) {
                JobApplication::create(['jid' => $v, 'cid' => $job->cid, 'mid' => $mid]);
                $company = Companys::find($job->cid);
                if ($company->unionid && config('app.env') == 'production') {
                    $officials = Official::where('unionid', $company->unionid)->where('status', 1)->first();
                    $read_no = JobApplication::where('cid', $company->id)->where('read_flg', 1)->count();
                    if ($officials->openid) {
                        //发送微信公众号通知
                        $wxNoticeData = [
                            'openid' => $officials->openid,
                            'type' => 9,
                            'title' => '尊敬的用户，您收到了新的简历，请您留意查阅。',
                            'memo' => '您可以点击详情，立即查看简历！',
                            'key' => [
                                'keyword1' => '1份',//新简历,
                                'keyword2' => $read_no . '份'//未读,
                            ],
                        ];
                        $this->dispatch(new SendWxNotice($wxNoticeData));
                    }
                }
            }
        }
        return $this->success();
    }
}
