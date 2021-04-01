<?php

namespace App\Http\Controllers\Index;

use App\Models\Application;
use App\Models\Companys;
use App\Models\Notice;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ApplicationController extends Controller
{
    //
    public function buy(Request $request){
        $vip_id = $request->get('vip_id','');
        $token = $request->get('token','');
        $company_tel  = $request->get('company_tel','');
        $company_name = $request->get('company_name','');
        $ip = $request->getClientIp();
        $company = null;
        if($token){
            $company = Companys::where('token',$token)->first();
            if($company){
                //通过cid 判断是否30秒内请求
                $data = Application::where(['cid' => $company->id,'vip_id' => $vip_id])->orderBy('id','desc')->first();
                if($data){
                    if((time() - strtotime($data['created_at'])) < 60 ){
                        return $this->fail(100007);
                    }
                }
                $company_name = $company->company_name;
                $company_tel = $company->phone;
                $datas['cid'] = $company->id;
            }else{
                //通过IP 判断是否30秒内请求
                $data = Application::where(['ip' => $ip,'vip_id' => $vip_id])->orderBy('id','desc')->first();
                if($data){
                    if((time() - strtotime($data['created_at'])) < 60 ){
                        return $this->fail(100007);
                    }
                }
            }
        }
        $datas['vip_id'] = $vip_id;
        $datas['ip'] = $ip;
        $datas['company_tel'] = $company_tel;
        $datas['company_name'] = $company_name;
        //记录申请
        Application::create($datas);
        if($token){
            Notice::addNotice(returnNoticeMsg(['company_name' => $company_name,'phone' => $company_tel],2011),2,2011);
        }else{
            Notice::addNotice(returnNoticeMsg(['company_name' => $company_name,'phone' => $company_tel],2012),2,2012);
        }
        //发送飞书通知
        if(config('app.env') == 'production') {
            //获取运营部通知手机好
            $phones = $this->getYunYingUserPhone();
            //获取通知内容
            $Feishu['company_name'] = $company_name;
            $Feishu['phone'] = $company_tel;
            $this->FeiShuSendText($phones,returnFeiShuMsg(isset($datas['cid']) ? 12 : 13 ,$Feishu));
        }
        return $this->success();
    }
}
