<?php

namespace App\Console\Commands;

use App\Models\CompanyAdvier;
use App\Models\Companys;
use App\Models\ImUser;
use Illuminate\Console\Command;
use Tencent;

class SendTextToUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'SendTextToUser';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '发送消息给企业和外教用户每周';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        //
        $list = ImUser::whereIn('type',[1,2])->get();
//        $list = ImUser::whereIn('type',[1])->where('user_id',37)->get();
        foreach ($list as $k => $v){
            if($v->type == 1){
                //发送消息
                $imUser = ImUser::where('user_id',15)->where('type',3)->first();
                $sendMsgRes = $this->sendImMsg(
                    config('app.env').'_'.$imUser->id,
                    config('app.env').'_'.$v->id,
                    'Welcome to Apex Global, find a fit job and right employer.'
                );
                $sendMsgRes = json_decode($sendMsgRes,true);
                if($sendMsgRes['ActionStatus']!='OK'){
                    echo 'IM消息发送失败,错误码:' . json_encode($sendMsgRes);
                }
            }else{
                //发送消息
                $imUser = ImUser::where('user_id', 12)->where('type', 3)->first();
                $sendMsgRes = $this->sendImMsg(
                    config('app.env') . '_' . $imUser->id,
                    config('app.env') . '_' . $v->id,
                    '欢迎入驻寰球阿帕斯外教招聘平台，很高兴为您服务。'
                );
                $sendMsgRes = json_decode($sendMsgRes, true);
                if ($sendMsgRes['ActionStatus'] != 'OK') {
                    echo 'IM消息发送失败,错误码:' . json_encode($sendMsgRes);
                }
                $company = Companys::find($v->user_id);
                if($company->status == 2){
                    //查找是否有顾问
                    $advert = CompanyAdvier::where('cid',$company->id)->first();
                    //查找顾问在im中的ID
                    $imUser1 = ImUser::where('user_id', $advert->uid)->where('type', 3)->first();
                    if($imUser1){
                        $sendMsgRes = $this->sendImMsg(
                            config('app.env') . '_' . $imUser1->id,
                            config('app.env') . '_' . $v->id,
                            '您好，我是您的专属顾问，您有任何问题都可以咨询我。'
                        );
                        $sendMsgRes = json_decode($sendMsgRes, true);
                        if ($sendMsgRes['ActionStatus'] != 'OK') {
                            echo 'IM消息发送失败,错误码:' . json_encode($sendMsgRes);
                        }
                    }
                }


            }
        }
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
}
