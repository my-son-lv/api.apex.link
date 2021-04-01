<?php

namespace App\Http\Controllers\Admin;

use App\Models\Companys;
use App\Models\Contract;
use App\Models\Files;
use App\Models\Interview;
use App\Models\SignContract;
use App\Models\SignContractLog;
use App\Models\SignContractUrge;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use PDF;
use SnappyImage;

class SignContractController extends Controller
{
    //签约管理列表
    public function list(Request $request){
        $page       =   $request->get('page',1);
        $pageSize   =   $request->get('pageSize',15);

        $list = SignContract::from('sign_contracts as a')
            ->leftjoin('companys as b','a.cid','=','b.id')
            ->leftjoin('user as c','a.user_id','=','c.id');
        $request->name && $list = $list->where('a.name','like',"%{$request->name}%");
        $request->start_time && $list = $list->where('a.start_date','>',$request->start_time.' 00:00:00');
        $request->end_time && $list = $list->where('a.start_date','<',$request->end_time.' 23:59:59');
        if($request->status){
            //1草稿 2待签署 3已完成 4未完成(4已拒绝 5已逾期 6已撤回)
            if($request->status == 4){
                $list = $list->whereIn('a.status',[4,5,6]);
            }else{
                $list = $list->where('a.status',$request->status);
            }
        }
        $count = ceil($list->count()/$pageSize);
        $list = $list->orderBy('id','desc')
            ->offset(($page-1)*$pageSize)
            ->limit($pageSize)
            ->get(['a.*','b.company_name','c.name as user_name']);
        return $this->success(['count' => $count,'list' => $list,'page' => $page]);
    }
    //详情
    public function signDesc(Request $request){
        $id     = $request->get('id','');
        if(!$id){
            return $this->fail(2000201);
        }
        $model = SignContract::find($id);
        $log = SignContractLog::where('sign_id',$model->id)->orderBy('id','desc')->get();
        $model->company = [];
        if($model->cid){
            $model->company = Companys::find($model->cid,['id','company_name']);
        }
        return $this->success(['model' => $model ,'log' => $log]);
    }
    //预览
    public function signView(Request $request){
        $id = $request->get('id','');
        if(!$id){
            return $this->fail(2000001);
        }
        $model = SignContract::find($id);
        if(!$model){
            return $this->fail(2000201);
        }
        if($model->status == 3){
            $file = Files::find($model->pdf_file_id);
            return $this->success(['url' => $file->path]);
        }elseif(in_array($model->status,[2, 4, 5, 6])){
            return $this->success(['url' => config('app.url').'/contractTmp?id='.AesEncrypt($model->id)]);
        }
    }

    public function contractTmp(Request $request){
        $id = $request->get('id','');
        if(!$id){
            return '';
        }
        $id = AesDecrypt($id);
        if(!$id){
            return '';
        }
        $model = SignContract::find($id);
        $tmp = Contract::find($model->contract_id);
        $contract_json = json_decode($model->contract_data,true);
        return pdf::loadView($tmp->path,compact('contract_json','model'))->setOption('encoding', "utf-8")->setPaper('a4')->inline();
    }

    //发起签约
    public function sign(Request $request){
        $id     = $request->get('id','');
        $type   = $request->get('type',1);//1草稿 2发起
        DB::beginTransaction();
        try{
            $onlyData = $request->only([
                'interview_id',
                'contract_id',
                'name',
                'a_name',
                'a_phone',
                'b_name',
                'b_phone',
                'b_company_name',
                'end_time',
                'memo',
                'notice',
                'auth_type',
                'contract_data',
            ]);
            if(@$onlyData['interview_id']){
                $ms = Interview::find($onlyData['interview_id']);
                $onlyData['cid'] = $ms->cid;
            }
            $onlyData['status'] = $type==1 ? 1 : 2;
            $type==2 &&  $onlyData['start_date'] = date("Y-m-d H:i:s",time());
            $onlyData['user_id'] = $request->user->id;

            if($type == 2){
                $arr = explode(',',$onlyData['notice']);
                //发送短信
                if(in_array(1,$arr)){

                }
                //发送站内信
                if(in_array(2,$arr)){

                }
            }
            if($id){
                $model = SignContract::find($id);
                if(!$model){
                    return $this->fail(2000201);
                }
//                $tmp = Contract::find($onlyData['contract_id'] ?? $model->contract_id);
//                $tmp_data = json_decode($tmp->contract_data,true);
//                $arr1 = [];
//                if($model->contract_data){
//                    $sign_contract_data = json_decode($model->contract_data,true);
//                    foreach ($tmp_data as $k => $v) {
//                        $arr1[$v] = $sign_contract_data[$v];
//                        $request->$v && $arr1[$v] = (string)$_POST[$v];
//                    }
//                }
//                $onlyData['contract_data'] = json_encode($arr1);
                $model->update($onlyData);
                $model = SignContract::find($id);
            }else{
//                $tmp = Contract::find($onlyData['contract_id']);
//                $tmp_data = json_decode($tmp->contract_data,true);
//                $arr1 = [];
//                foreach ($tmp_data as $k => $v){
//                    if(!$id) $arr1[$v] = '';
//                    $request->$v && $arr1[$v] = (string)$_POST[$v];
//                }
//                count($arr1)  && $onlyData['contract_data'] = json_encode($arr1);
                $model = SignContract::create($onlyData);
            }
            if($model){
                if($type == 2) {
                    //校验字段
                    if (!$model->contract_id ||
                        !$model->name ||
                        !$model->a_name ||
                        !$model->a_phone ||
                        !$model->a_phone ||
                        !$model->b_name ||
                        !$model->b_phone ||
                        !$model->b_company_name ||
                        !$model->end_time ||
                        !$model->notice
                    ) {
                        DB::rollBack();
                        return $this->fail(100001);
                    }
                    //校验合同参数
                    $tmp = Contract::find($model->contract_id);
                    $json = json_decode($tmp->contract_data,true);
                    $contract_json = json_decode($model->contract_data,true);
//                    if($model->contract_data){
//                        $contract_json = json_decode($model->contract_data,true);
//                        foreach ($json as $k => $v){
//                            if(!$contract_json[$v]){
//                                if(substr($v,0,3) == 'bus'){
//                                    continue;
//                                }
//                                DB::rollBack();
//                                return $this->fail(100001);
//                            }
//                        }
//                    }else{
//                        DB::rollBack();
//                        return $this->fail(100001);
//                    }
                    //记录发起日志
                    SignContractLog::addLog($request->user->id,1,$model->id,'由'.$request->user->name.'发起');

                    //创建企业  个人账户
                    $token = $this->eSignAutoLogin();
                    $user_reg_id = 'user_'.date('YmdHis').rand(100000,999999);
                    $accoountId = $this->eSignAutoCrateUser($user_reg_id,$model->b_name,$model->b_phone,$token);
                    if($model->auth_type == 1){
                        $org_reg_id = 'org_'.date('YmdHis').rand(100000,999999);
                        $org_id = $this->eSignAutoCrateOrganize($org_reg_id,$model->b_compnay_name,$accoountId,$token);
                    }else{
                        $org_id = '';
                    }

                    //生成pdf
                    $tmp_pdf_name = date("YmdHis",time()).rand(1000000,9999999).'.pdf';
                    $tmp_pdf_path = storage_path().'/tmp/'.$tmp_pdf_name;
                    pdf::loadView($tmp->path,compact('contract_json','model'))->setOption('encoding', "utf-8")->save($tmp_pdf_path);
                    //获取上传地址
                    $contentMD5 = base64_encode(md5_file($tmp_pdf_path,true));
                    $res = $this->eSignGetUploadUrl($token,$contentMD5,$tmp_pdf_name,filesize($tmp_pdf_path));
                    $upload_url = $res['url'];
                    $file_id  = $res['fileId'];
                    //上传pdf
                    $this->eSignUploadFile($upload_url,$contentMD5,file_get_contents($tmp_pdf_path));
                    //删除pdf
                    unlink($tmp_pdf_path);
                    //一键发起签署
                    $xy2 = json_decode($tmp->sign_x_y,true);
                    $xy1 = json_decode($tmp->sign_user_x_y,true);
                    $xy = ['a' => $xy1,'b' => $xy2];
                    $res2 = $this->eSignCreateFlowOneStep($token,$accoountId, $org_id,$file_id,$model->auth_type==1 ? $model->name : $model->b_name,$model->end_time,$xy);
                    $flow_id = $res2['flowId'];

                    $model->user_reg_id = $user_reg_id;
                    $model->org_reg_id  = $model->auth_type == 1 ? $org_id : '';
                    $model->flow_id     = $flow_id;
                    $model->file_id     = $file_id;
                    $model->org_id      = $org_id;
                    $model->account_id  = $accoountId;
                    if(!$model->save()){
                        DB::rollBack();
                        Log::info('出错了 更新合同信息出错了');
                        return  $this->fail();
                    }
                }
                DB::commit();
                $model->cid && $model->company = Companys::find($model->cid,['id','company_name']);
                return $this->success(['model' => $model]);
            }else{
                DB::rollBack();
                Log::info('出错了 model == false');
                return  $this->fail();
            }
        }catch (\Exception $e) {
            //接收异常处理并回滚
            DB::rollBack();
            Log::info('出错了 行:'.$e->getLine().' 错误描述：'.$e->getMessage());
            return  $this->fail();
        }
    }

    //签约草稿
    public function getSignDraft(Request $request){
        $id = $request->get('id','');
        if(!$id) return $this->fail(100001);
        $data = SignContract::where('id',$id)->where('status',1)->first();
        if($data){
            return $this->success($data);
        }else{
            return $this->fail(2000201);
        }
    }
    //撤销
    public function signCancel(Request $request){
        $id = $request->get('id','');
        if(!$id) return $this->fail(100001);
        $data = SignContract::where('id',$id)->where('status',2)->first();
        if(!$data) return $this->fail(2000201);
        //撤销
        $token = $this->eSignAutoLogin();
        $flg = $this->eSignFlowCancel($token,$data->flow_id);
        if($flg){
            $data->status = 6;
            if($data->save()){
                //记录撤销日志
                SignContractLog::addLog($request->user->id,1,$id,'由'.$request->user->name.'撤销');
                return $this->success();
            }else{
                return $this->fail();
            }
        }else{
            return $this->fail();
        }
    }
    //催办
    public function signUrge(Request $request){
        $notice = $request->get('notice','');
        $id     = $request->get('id','');
        if(!$id && !$notice){
            return $this->fail(100001);
        }
        $data = SignContract::where('id',$id)->where('status',2)->first();
        if(!$data) return $this->fail(2000201);
        $arr = explode(',',$notice);
        //发送短信
        if(in_array(1,$arr)){

        }
        //发送站内信
        if(in_array(2,$arr)){

        }
        $token = $this->eSignAutoLogin();
        $flg = $this->eSignFlowUrge($token,$data->flow_id,$data->account_id);
        if($flg){
            DB::beginTransaction();
            try{
                $model = SignContractUrge::create([
                    'user_id' => $request->user->id,
                    'notice'  => $notice,
                    'sign_id' => $id,
                ]);
                //记录发起日志
                $log_flg = SignContractLog::addLog($request->user->id,1,$id,$request->user->name.'发送了催办通知');
                if($log_flg && $model){
                    DB::commit();
                    return $this->success();
                }else{
                    DB::rollBack();
                    return  $this->fail();
                }
            }catch (\Exception $e) {
                //接收异常处理并回滚
                DB::rollBack();
                Log::info('出错了 行:'.$e->getLine().' 错误描述：'.$e->getMessage());
                return  $this->fail();
            }
        }else{
            return $this->fail();
        }

    }

    public function eSignAutoNotify(Request $request){
        $ip = $request->getClientIp();
        /*if(!in_array($ip,['47.96.79.204','118.31.35.8'])){
            return false;
        }*/
        $data = $request->getContent();
        echo 200;
        Log::info('e签宝回调结果'.$data);
        $data = json_decode($data,true);
        switch ($data['action']){
            //用户签署时间
            /*case 'SIGN_FLOW_UPDATE':
                echo "200";
                break;*/
            //流程结束
            case 'SIGN_FLOW_FINISH':
                $model = SignContract::where('flow_id',$data['flowId'])->where('status',2)->first();
                if(!$model){
                    die();
                };
                $log = new SignContractLog();
                $log->sign_id = $model->id;
                switch ($data['flowStatus']){
                    case 2://已完成
                        $log->type = 2;
                        $log->info = $model->b_name.' 签署了 《'.$model->name."》";
                        $model->status = 3;
                        $token = $this->eSignAutoLogin();
                        //获取文件下载地址
                        $downFile = $this->eSignGetContractDownUrl($token,$model->flow_id);
                        //上传阿里云 添加合同
                        $fileName = date("YmdHis").rand(10000000,99999999).'.pdf';
                        $fileMime = 'application/pdf';
                        $upFilePathName = '/'.config('filesystems.disks.oss.bucket').'/'.$fileName;
                        $fileUpFlg = Storage::disk('oss')->put($upFilePathName,file_get_contents($downFile['fileUrl']));
                        $filePath = Storage::disk('oss')->url($upFilePathName);
                        #插入数据库
                        $file_model = Files::addFiles($fileName,0,'pdf',$filePath,$fileMime,$fileName);
                        $model->pdf_file_id = $file_model->id;
                        break;
                    case 3://已撤销
                        break;
                    case 5://已过期
                        $log->type = 3;
                        $log->info = "合同已过期";
                        $model->status = 5;
                        break;
                    case 7://已拒签
                        $log->type = 2;
                        $log->info = "合同已拒签:".$data['statusDescription'];
                        $model->status = 4;
                        $model->info = $data['statusDescription'];
                        break;
                }
                DB::beginTransaction();
                if($model->save() && $log->save()){
                    DB::commit();
                }else{
                    DB::rollback();
                    Log::info('合同通知出错了');
                }
        }
    }
}
