<?php

namespace App\Http\Controllers\Admin;

use App\Jobs\SendWxNotice;
use App\Models\Companys;
use App\Models\Job;
use App\Models\Official;
use App\Models\Vip;
use App\Models\VipAction;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class VipActionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(VipAction $vipAction , Request $request)
    {
        $vipAction = $vipAction->with(['company','vip','user']);
        $request->company_name && $vipAction = $vipAction->whereHas('company',function ($query) use ($request) {
            $query->where('company_name','like',"%{$request->company_name}%");
        });
        $request->vip_type && $vipAction = $vipAction->where('vip_id',$request->vip_type);
        $request->status && $vipAction = $vipAction->where('status',$request->status);
        return $vipAction->orderBy('id','desc')->paginate($request->pageSize ?? $request->pageSize);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(VipAction $vipAction,Request $request)
    {
        $user_id = $request->user->id;
        $data = $request->all();
        $data['user_id'] = $user_id;
        unset($data['token']);
        unset($data['user']);
        $vipAction = $vipAction->create($data);
        return $vipAction;
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(VipAction $vipAction)
    {
        //
        $vipaction =  $vipAction->load(['company','vip','user']);
        $vipAction->yy_job_num = Job::where('cid',$vipAction->company->id)->count();
        return $vipaction;
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request,VipAction $vipAction)
    {
        $data = $request->only(['status']);
        //开启
        if($data['status']==2 || $data['status'] == 3){
            DB::beginTransaction();
            $vip = Vip::find($vipAction->vip_id);
            $company = Companys::find($vipAction->cid);
            try {
                if($data['status'] == 2){
                    //计算开始 结束时间
                    $data['start_time'] = date("Y-m-d 00:00:00",time());
                    $data['end_time'] = date("Y-m-d 23:59:59",strtotime("+ ". $vip->month ." month",time()));
                    Companys::where('id',$vipAction->cid)->update(['vip_actions_id' => $vipAction->id]);
                    //计算已用职位数
                    $data['yy_job_num'] = Job::where('cid',$vipAction->cid)->where('flg',1)->count();
                    //发送短信通知用户
                    $this->aliyunSendSms($company->phone,'SMS_205888539');
                    //发送微信通知
                    if($company->unionid && config('app.env') == 'production'){
                        $officials = Official::where('unionid',$company->unionid)->where('status',1)->first();
                        if($officials){
                            //发送微信通知
                            $wxNoticeData = [
                                'openid' => $officials->openid,
                                'type' =>  1,
                                'title' => '恭喜您成为寰球阿帕斯会员，您将享有平台会员权益。',
                                'memo'  => '如有疑问请联系您的顾问。',
                                'key' => [
                                    'keyword1' => $company->company_name,
                                    'keyword2' => date("Y年m月d日 H:i",strtotime($data['start_time'])),
                                    'keyword3' => date("Y年m月d日 H:i",strtotime($data['end_time'])) ,
                                ],
                            ];
                            $this->dispatch(new SendWxNotice($wxNoticeData));
                        }
                    }
                }elseif($data['status'] == 3){//关闭
                    Companys::where('id',$vipAction->cid)->update(['vip_actions_id' => null]);
                    //职位全部关闭  置顶取消
                    Job::where('cid',$vipAction->cid)->update(['top' => 0,'top_exp_time' => null ,'status' => 2]);
                }
                $vipAction->update($data);
                DB::commit();
                return $vipAction;
            }catch (\Exception $e){
                DB::rollback();
                Log::info('状态修改失败'.$e->getMessage());
                return $this->fail();
            }
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
