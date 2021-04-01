<?php

namespace App\Http\Controllers\Index;

use App\Models\Down;
use App\Models\Job;
use App\Models\Vip;
use App\Models\VipAction;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class VipController extends Controller
{
    #获取平台数据
    public function getPlatData(Request $request){
        $data = array(
            'yy_job_num' => Job::where('cid',$request->company->id)->where('status',1)->count(),
            'yy_top'     => 0,
            'yy_down'    => Down::where('cid',$request->company->id)->whereNull('vip_id')->count(),
            'yy_jiping'  => 0,
            'yy_yaoqing' => 0,
            'yy_tuisong' => 0,
            'job_num' => 1,
            'top'     => 0,
            'down'    => 0,
            'jiping'  => 0,
            'yaoqing' => 0,
            'tuisong' => 0,
        );
        if($request->company->vip_actions_id){
            $action = VipAction::find($request->company->vip_actions_id);
            $data['yy_job_num'] = $action->yy_job_num;
            $data['yy_down'] = $action->yy_down;
            $data['yy_top'] = $action->yy_top;
            $data['yy_jiping'] = $action->yy_jiping;
            $data['yy_yaoqing'] = $action->yy_yaoqing;
            $data['yy_tuisong'] = $action->yy_tuisong;
            $vip = Vip::find($action->vip_id);

            $data['job_num'] = $vip->job_num;
            $data['down'] = $vip->down;
            $data['top'] = $vip->top;
            $data['jiping'] = $vip->jiping;
            $data['yaoqing'] = $vip->yaoqing;
            $data['tuisong'] = $vip->tuisong;
        }
        return $this->success($data);
    }
}
