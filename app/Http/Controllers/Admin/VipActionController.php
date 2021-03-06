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
        //??????
        if($data['status']==2 || $data['status'] == 3){
            DB::beginTransaction();
            $vip = Vip::find($vipAction->vip_id);
            $company = Companys::find($vipAction->cid);
            try {
                if($data['status'] == 2){
                    //???????????? ????????????
                    $data['start_time'] = date("Y-m-d 00:00:00",time());
                    $data['end_time'] = date("Y-m-d 23:59:59",strtotime("+ ". $vip->month ." month",time()));
                    Companys::where('id',$vipAction->cid)->update(['vip_actions_id' => $vipAction->id]);
                    //?????????????????????
                    $data['yy_job_num'] = Job::where('cid',$vipAction->cid)->where('flg',1)->count();
                    //????????????????????????
                    $this->aliyunSendSms($company->phone,'SMS_205888539');
                    //??????????????????
                    if($company->unionid && config('app.env') == 'production'){
                        $officials = Official::where('unionid',$company->unionid)->where('status',1)->first();
                        if($officials){
                            //??????????????????
                            $wxNoticeData = [
                                'openid' => $officials->openid,
                                'type' =>  1,
                                'title' => '????????????????????????????????????????????????????????????????????????',
                                'memo'  => '????????????????????????????????????',
                                'key' => [
                                    'keyword1' => $company->company_name,
                                    'keyword2' => date("Y???m???d??? H:i",strtotime($data['start_time'])),
                                    'keyword3' => date("Y???m???d??? H:i",strtotime($data['end_time'])) ,
                                ],
                            ];
                            $this->dispatch(new SendWxNotice($wxNoticeData));
                        }
                    }
                }elseif($data['status'] == 3){//??????
                    Companys::where('id',$vipAction->cid)->update(['vip_actions_id' => null]);
                    //??????????????????  ????????????
                    Job::where('cid',$vipAction->cid)->update(['top' => 0,'top_exp_time' => null ,'status' => 2]);
                }
                $vipAction->update($data);
                DB::commit();
                return $vipAction;
            }catch (\Exception $e){
                DB::rollback();
                Log::info('??????????????????'.$e->getMessage());
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
