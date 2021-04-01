<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Mrgoon\AliSms\AliSms;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Tencent;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * 获取要通知的运营部人员手机号
     * @param int[] $user [7,14,17]李亚静 周婷婷 安奎
     * @return mixed 返回手机号['13521339391','...']
     */
    public function getYunYingUserPhone($user  = [7,14,17,24]){
        $phone  = User::whereIn('id',$user)->pluck('phone')->toArray();
        array_push($phone,'18622283999');
        return $phone;
    }

    /**
     * 飞书发送通知
     * @param $mobiles ['13521339391','17744499986']
     * @param string $text  '发送的信息的内容'
     * @return bool  true 成功 false 失败
     */
    public function FeiShuSendText($mobiles,$text = '')
    {
        $login_url = 'https://open.feishu.cn/open-apis/auth/v3/app_access_token/internal?app_id=cli_9f29df329cf1500b&app_secret=6HliNKv4gq1EdXgtaJhAhcwGohXC04Ac';
        $user = json_decode(file_get_contents($login_url),true);
        if($user['code'] === 0){
            // $mobiles = ['13521339391','17744499986'];
            // $mobiles = $mobiles;
            $url_param = '?';
            foreach ($mobiles as $v) {
                $url_param .= 'mobiles='.$v.'&';
            }
            $url_param = substr($url_param,0,-1);
            $header = array('Authorization:Bearer '.$user['tenant_access_token']);
            $user_mobildes = 'https://open.feishu.cn/open-apis/user/v1/batch_get_id'.$url_param ;
            $user_list = json_decode(postJsonCurl($user_mobildes,[],$header),true);
            if($user_list['code'] === 0){
                $user_arr = [];
                foreach ($user_list['data']['mobile_users'] as $k => $v) {
                    array_push($user_arr,$v[0]['user_id']);
                }
                $send_text_url = 'https://open.feishu.cn/open-apis/message/v4/batch_send/';
                $param = array("user_ids" => $user_arr , "msg_type" => "text", "content" => array("text" => $text));
                $user_send = json_decode(postJsonCurl($send_text_url,$param,$header,"GET"),true);
                if($user_send['code'] === 0){
                    return true;
                }else{
                    Log::info('发送信息失败:'.json_encode($user_send));
                    return false;
                }
            }else{
                Log::info('获取用户信息失败:'.json_encode($user_list));
                return false;
            }
        }else{
            Log::info('获取token失败:'.json_encode($user));
            return false;
        }
    }

    /*public function sendNoticeToUser($phone = '18639008740' ){
        $smsFlg = $this->aliyunSendSms($phone,'SMS_205123498');
        if(!$smsFlg['status']){
            Log::info('发送失败'.$smsFlg['msg']);
        }
    }*/


    public function eSignGetPage($token,$flowId,$accountId){
        $header = array(
            "Content-Type: application/json",
            "X-Tsign-Open-App-Id:".config('esign.E_SIGN_APP_ID'),
            "X-Tsign-Open-Token:".$token,
        );
        $url = config('esign.E_SIGN_AUTO_URL').'/v1/signflows/'.$flowId.'/executeUrl?accountId='.$accountId.'&organizeId=0&urlType=0';
        $res = json_decode(postJsonCurl($url,[],$header,$type="GET"),true);
        if($res['code'] == 0){
            return $res['data']['shortUrl'];
        }else{
            Log::info('e签宝错误_PDF下载路径：'.json_encode($res));
            return false;
        }
    }
    //催办
    public function eSignFlowUrge($token,$flowId,$accountId){
        $header = array(
            "Content-Type: application/json",
            "X-Tsign-Open-App-Id:".config('esign.E_SIGN_APP_ID'),
            "X-Tsign-Open-Token:".$token,
        );
        $data = [
            "noticeTypes"   => 1,
        ];
        $url = config('esign.E_SIGN_AUTO_URL').'/v1/signflows/'.$flowId.'/signers/rushsign';
        $res = json_decode(doPut($url,$data,$header),true);
        if($res['code'] == 0){
            return true;
        }else{
            Log::info('e签宝错误_合同催办：'.json_encode($res));
            return false;
        }
    }
    //撤销
    public function eSignFlowCancel($token,$flowId){
        $header = array(
            "Content-Type: application/json",
            "X-Tsign-Open-App-Id:".config('esign.E_SIGN_APP_ID'),
            "X-Tsign-Open-Token:".$token,
        );
        $data = [
            "flowId"    => $flowId,
        ];
        $url = config('esign.E_SIGN_AUTO_URL').'/v1/signflows/'.$flowId.'/revoke';
        $res = json_decode(doPut($url,$data,$header),true);
        if($res['code'] == 0){
            return true;
        }else{
            Log::info('e签宝错误_合同撤销：'.json_encode($res));
            return false;
        }
    }
    //合同文件
    public function eSignGetContractDownUrl($token,$flowId){
        $header = array(
            "Content-Type: application/json",
            "X-Tsign-Open-App-Id:".config('esign.E_SIGN_APP_ID'),
            "X-Tsign-Open-Token:".$token,
        );
        $url = config('esign.E_SIGN_AUTO_URL').'/v1/signflows/'.$flowId.'/documents';
        $res = json_decode(postJsonCurl($url,[],$header,$type="GET"),true);
        if($res['code'] == 0){
            return $res['data']['docs'][0];
        }else{
            Log::info('e签宝错误_PDF下载路径：'.json_encode($res));
            return false;
        }
    }

    /**
     * 一键发起签署
     * @param $id   用户id
     * @param $org_id 企业用户id
     * @param $file_id 文件id
     * @param $file_name 文件名称
     * @param $end_date  截止时间
     * @return bool|mixed
     */
    public function eSignCreateFlowOneStep($token,$id,$org_id = '' ,$file_id,$file_name,$end_date,$xy){
        $header = array(
            "Content-Type: application/json",
            "X-Tsign-Open-App-Id:".config('esign.E_SIGN_APP_ID'),
            "X-Tsign-Open-Token:".$token,
        );
        $data = [
            'docs'      => [['fileId' => $file_id,'fileName' => $file_name]],
            'flowInfo'  => [
                'autoArchive' => true,
                'autoInitiate' => true,
                'businessScene' => $file_name,
                'signValidity'  => strtotime(date('Y-m-d 23:59:59',strtotime($end_date))) * 1000 ,
                    'flowConfigInfo' => [
                        'noticeType' => 1,
                        'noticeDeveloperUrl' => config('app.url').'/api/eSign/eSignAutoNotify',
                        'redirectUrl'        => config('app.web_site_url'),
                        'signPlatform'       => 1,
                    ],
                ],
            'signers'   => [
                [
                    'platformSign' => false,
                    'signOrder' => 1 ,
                    'signerAccount' => ['signerAccountId' => $id , 'authorizedAccountId' => $org_id ?? $id],
                    'signfields' => [
                        ['autoExecute' => false , 'actorIndentityType' => 2 , 'fileId' => $file_id,
                                'posBean' => [ 'posPage' => $xy['a']['page'] ?? 1 , 'posX' => $xy['a']['x'] ?? 201 , 'posY' => $xy['a']['y'] ?? 406 ],
                        ]
                    ]
                ],
                [
                    'platformSign' => true,
                    'signOrder'    => 2,
                    'signfields'   => [
                        ['autoExecute' => true , 'actorIndentityType' => 2 , 'fileId' => $file_id ,
                            'posBean' => [ 'posPage' => $xy['b']['page'] ?? 1 , 'posX' => $xy['b']['x'] ?? 607 , 'posY' => $xy['b']['y'] ?? 395 ],
                        ]
                    ],
                ]
            ]
        ];
        if(!$org_id){
            unset($data['signers'][0]['signerAccount']['authorizedAccountId']);
            unset($data['signers'][0]['signfields'][0]['actorIndentityType']);
        }
        Log::info('e签宝请求参数'.json_encode($data));
        $url = config('esign.E_SIGN_AUTO_URL').config('esign.E_SGIN_CREATE_FLOW_ONE_STEP');
        $res = json_decode(postJsonCurl($url,$data,$header),true);
        if($res['code'] == 0){
            return ['flowId' => $res['data']['flowId']] ;
        }else{
            Log::info('e签宝错误_一键发起签署：'.json_encode($res));
            return false;
        }
    }


    //put上传文件流
    public function eSignUploadFile($uploadUrls, $contentMd5, $fileContent){
        $res = sendHttpPUT($uploadUrls,$contentMd5,$fileContent);
        return $res;
    }

    //获取上传文件url
    public function eSignGetUploadUrl($token,$pdf_md5,$pdf_name,$pdf_size){
        $header = array(
            "Content-Type: application/json",
            "X-Tsign-Open-App-Id:".config('esign.E_SIGN_APP_ID'),
            "X-Tsign-Open-Token:".$token,
        );
        $data = [
            "contentMd5"    =>$pdf_md5,
            "contentType"   =>"application/pdf",
            "convert2Pdf"   =>false,
            "fileName"      =>$pdf_name,
            "fileSize"      =>$pdf_size //btye
        ];
        $url = config('esign.E_SIGN_AUTO_URL').config('esign.E_SGIN_GET_UPLOAD_URL');
        $res = json_decode(postJsonCurl($url,$data,$header),true);
        if($res['code'] == 0){
            return ['url' => $res['data']['uploadUrl'],'fileId' =>$res['data']['fileId'] ];
        }else{
            Log::info('e签宝错误_获取文件上传URL：'.json_encode($res));
            return false;
        }
    }

    //登录获取token
    public function eSignAutoLogin(){
//        $token = Cache::get('e_sign_token');
        /*if($token){
            return  $token;
        }else{*/
            $url = config('esign.E_SIGN_AUTO_URL').config('esign.E_SIGN_AUTO_LOGIN_URL').'?grantType=client_credentials&secret='.config('esign.E_SIGN_SECRET').'&appId='.config('esign.E_SIGN_APP_ID');
            $data = json_decode(file_get_contents($url),true);
            if($data['code'] == 0){
                Cache::put('e_sign_token',$data['data']['token'], 1.6 * 60 * 60);
                return $data['data']['token'];
            }else{
                Log::info('e签宝错误_获取token：'.json_encode($res));
                return false;
            }
//        }
    }

    /**
     * 创建个人账户
     * @param $id 用户唯一标识
     * @param $name 用户姓名
     * @return bool|mixed
     */
    public function eSignAutoCrateUser($id,$name,$mobile,$token){
        $header = array(
            "Content-Type: application/json",
            "X-Tsign-Open-App-Id:".config('esign.E_SIGN_APP_ID'),
            "X-Tsign-Open-Token:".$token,
        );
        $data = [
            'thirdPartyUserId' => $id,
            'name'             => $name,
            'mobile'           => $mobile,
        ];
        $url = config('esign.E_SIGN_AUTO_URL').config('esign.E_SIGN_AUTO_CREATE_USER_URL');
        $res = json_decode(postJsonCurl($url,$data,$header),true);
        if($res['code'] == 0){
            return $res['data']['accountId'];
        }else{
            Log::info('e签宝错误_创建个人账户：'.json_encode($res));
            return false;
        }
    }

    /**
     * 实名认证企业用户
     * @param $id  企业用户唯一标识
     * @param $name 企业名称
     * @param $accountId 个人账号accountId
     * @return bool|mixed
     */
    public function eSignAutoCrateOrganize($id,$name,$accountId){
        $token = $this->eSignAutoLogin();
        $header = array(
            "Content-Type: application/json",
            "X-Tsign-Open-App-Id:".config('esign.E_SIGN_APP_ID'),
            "X-Tsign-Open-Token:".$token,
        );
        $data = [
            'thirdPartyUserId' => $id,
            'creator'          => $accountId,
            'name'             => $name,
        ];
        $url = config('esign.E_SIGN_AUTO_URL').config('esign.E_SGIN_AUTO_CREATE_ORGANIZE_URL');
        $res = json_decode(postJsonCurl($url,$data,$header),true);
        if($res['code'] == 0){
            return $res['data']['orgId'];
        }else{
            Log::info('e签宝错误_创建企业账户：'.json_encode($res));
            return false;
        }
    }

    public function eSignAutoGetUrl($orgId,$accountId,$token){
        $header = array(
            "Content-Type: application/json",
            "X-Tsign-Open-App-Id:".config('esign.E_SIGN_APP_ID'),
            "X-Tsign-Open-Token:".$token,
        );
        $data = [
            'agentAccountId' => $accountId,    //办理人accountId
            'contextInfo'    => [
                'notifyUrl'         => config('app.url').'/api/eSign/eSignAutoNotify',//异步通知
                "redirectUrl"       => config('app.url'),//实名结束后页面跳转地址
                "showResultPage"    => "true" //实名完成是否显示结果页,默认显示
            ]
        ];
        $url = config('esign.E_SIGN_AUTO_URL').'/v2/identity/auth/web/'.$orgId.'/orgIdentityUrl';
        $res = json_decode(postJsonCurl($url,$data,$header),true);
        if($res['code'] == 0){
            return ['url' => $res['data']['shortLink'],'flowId' => $res['data']['flowId']];
        }else{
            Log::info('e签宝错误：'.json_encode($res));
            return false;
        }
    }




    public function createQrCode(Request $request){
        $img =  QrCode::format('png')->size(200)->generate($request->url ? $request->url : '');
        $type = getimagesizefromstring($img)['mime']; //获取二进制流图片格式
        $img_base64 = 'data:' . $type . ';base64,' . chunk_split(base64_encode($img));
        return $this->success([
            'img'      => $img_base64,
        ]);
    }

    /**
     * 返回默认头像 1外教 2企业 3运营后台 4外教男  5外教女
     * @param int $type
     * @return array
     */
    public function getDefaultLogo($type = 1){
        $img = [[
            'name'  => '',
            'ext'   => 'png',
            'path'  => '',
        ]];
        switch ($type){
            case 1:
                $img[0]['name'] = 'teach_default_logo.png';
                $img[0]['path'] = config('app.url').'/logo/teach_defaut_logo.png';
                break;
            case 2:
                $img[0]['name'] = 'company_defaut_logo.png';
                $img[0]['path'] = config('app.url').'/logo/company_defaut_logo.png';
                break;
            case 3:
                $img[0]['name'] = 'user_defaut_logo.png';
                $img[0]['path'] = config('app.url').'/logo/user_defaut_logo.png';
                break;
            case 4:
                $img[0]['name'] = 'teach_default_nan.png';
                $img[0]['path'] = config('app.url').'/logo/teach_default_nan.png';
                break;
            case 5:
                $img[0]['name'] = 'teach_default_nv.png';
                $img[0]['path'] = config('app.url').'/logo/teach_default_nv.png';
                break;
        }
        return $img;
    }

    /**
     * 生成唯一字符串
     * @param int $unique
     * @return string
     */
    public function genRequestSn($unique=0){
        $orderNo = substr(microtime(), 2, 5) . mt_rand(10000,99999);
        if(!empty($unique)) $orderNo = $orderNo.$unique;
        return $orderNo;
    }


    /**
     * 发送短信验证码
     *
     * @param $phone  手机号
     * @param $code   短信验证码
     * @param $smsTmplateCoe [SMS_205315358,SMS_205399963,SMS_205430147,SMS_205888539]
     * [
     * '验证码${code}，您正在进行身份验证，打死不要告诉别人哦！',
     * '您好企业端/外教端用户新提交申请。请登录后台审核。',
     * 'You have registered with Apex Global, verification code:${code}, valid for 5mins.'
     * '尊敬的用户，恭喜您成为寰球阿帕斯会员，登录小程序或官网企业服务系统尽享会员特权',
     * ]
     * @return bool
     */
    public function aliyunSendSms($phone,$smsTmplateCoe,$code = ''){
        $aly_sms = new AliSms();
        $res = $aly_sms->sendSms($phone,$smsTmplateCoe,$code ? ['code' => $code] : '');
        if ($res->Code == 'OK') {
            return ['status' => true,'msg' => $res->Message];
        }else{
            Log::info('短信验证码发送失败：'.$res->Message);
//            return false;
            return ['status' => false,'msg' => $res->Message];
        }
    }

    /**
     * 腾讯IM导入单个账号
     */
    public function createImOneAccount($user){
        /*$user = ['Identifier'=>'justin_test_123','Nick'=>'Justin33','FaceUrl'=> ''];*/
        $url = $this->settingUrl('v4/im_open_login_svc/account_import');
        return $this->postCurl($url,$user);
    }


    public function sendImMsg($from_user,$to_user,$text){
        $msg = [
            'SyncOtherMachine'  => 2,
            'From_Account'      => $from_user,
            'To_Account'        => $to_user,
//            'MsgLifeTime'       => 10, 默认7天
            'MsgRandom'         => rand(111111,999999999),
            'MsgTimeStamp'      => time(),
            'MsgBody'           => [[
                'MsgType'       => 'TIMTextElem',
                'MsgContent'    =>[
                    'Text'  => $text,
                ]
            ]]
        ];
        $url = $this->settingUrl('v4/openim/sendmsg');
        return $this->postCurl($url,$msg);
    }

    /*public function testSendImMsg(){
        $msg = [
            'SyncOtherMachine'  => 2,
            'From_Account'      => 'test_4',//$from_user,
            'To_Account'        => 'test_65',//$to_user,
            'MsgRandom'         => rand(111111,999999999),
            'MsgTimeStamp'      => time(),
            'MsgBody'           => [[
                'MsgType'       => 'TIMTextElem',
                'MsgContent'    =>[
                    'Text'  => '1111',//$text,
                ]
            ]]
        ];
        Log::info(json_encode($msg));
        $url = $this->settingUrl('v4/openim/sendmsg');
        return $this->postCurl($url,$msg);
    }*/



    public function success($data = [])
    {
        return response()->json([
//            'status'  => true,
            'code'    => 200,
            'msg' => config('errorcode.code')[200],
            'data'    => $data,
        ]);
    }
    public function fail($code = 100000, $msg = '')
    {
        return response()->json([
//            'status'  => false,
            'code'      => $code,
            'msg'       => $msg!=='' ? $msg : config('errorcode.code')[(int) $code],
//            'data'      => $data,
        ]);
    }

    /**
     * CURL Post发送数据
     *
     * @param $url 地址
     * @param $option 参数数据
     * @param $header 消息头
     * @param $type 发送方式
     */
    public function postCurl($url, $option, $header = 0, $type = 'POST') {
        $curl = curl_init (); // 启动一个CURL会话
        curl_setopt ( $curl, CURLOPT_URL, $url ); // 要访问的地址
        curl_setopt ( $curl, CURLOPT_SSL_VERIFYPEER, FALSE ); // 对认证证书来源的检查
        curl_setopt ( $curl, CURLOPT_SSL_VERIFYHOST, FALSE ); // 从证书中检查SSL加密算法是否存在
        curl_setopt ( $curl, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.0; Trident/4.0)' ); // 模拟用户使用的浏览器
        if (! empty ( $option )) {
            $options = json_encode ( $option );
            curl_setopt ( $curl, CURLOPT_POSTFIELDS, $options ); // Post提交的数据包
        }
        curl_setopt ( $curl, CURLOPT_TIMEOUT, 30 ); // 设置超时限制防止死循环
        curl_setopt ( $curl, CURLOPT_RETURNTRANSFER, 1 ); // 获取的信息以文件流的形式返回
        curl_setopt ( $curl, CURLOPT_CUSTOMREQUEST, $type );
        $result = curl_exec ( $curl ); // 执行操作
        curl_close ( $curl ); // 关闭CURL会话
        return $result;
    }

    /**
     * 生成url
     *
     * 单个账号导入：v4/im_open_login_svc/account_import
     * 批量账号导入：v4/im_open_login_svc/multiaccount_import
     * 删除账号：v4/im_open_login_svc/account_delete
     * 查询账号：v4/im_open_login_svc/account_check
     *
     * @param $apiUrl
     * @return \Illuminate\Config\Repository|mixed|string
     * @throws \Exception
     */
    public function settingUrl($apiUrl){
        $api = new Tencent\TLSSigAPIv2(config('videos.SDK_APP_ID'),config('videos.USER_SIG'));
        $sig = $api->genSig(config('videos.SDK_ADMIN_USER'));
        $url  = config('videos.IM_SERVICE_URL');
        $url .= $apiUrl;
        $url .= '?'.'sdkappid='.config('videos.SDK_APP_ID');
        $url .= '&'.'identifier='.config('videos.SDK_ADMIN_USER');
        $url .= '&'.'usersig='.$sig;
        $url .= '&'.'random='.rand(111111,999999999);
        $url .= '&contenttype=json';
        return $url;
    }
}
