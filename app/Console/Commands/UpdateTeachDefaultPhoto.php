<?php

namespace App\Console\Commands;

use App\Models\ImUser;
use App\Models\Member;
use App\Models\MemberInfoChecked;
use Illuminate\Console\Command;
use Tencent;


class UpdateTeachDefaultPhoto extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'UpdateTeachDefaultPhoto';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '修改教师默认头像';

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
        //查询是否提价 / 审核通过
        $list = Member::all();
        foreach ($list as $k => $v){
            $name = $v->nick_name;
            $photo = config('app.url').'/logo/teach_default_nan.png';
            //查询是否提交
            $teach = MemberInfoChecked::where('mid',$v->id)->first();
            if($teach && $teach->status == 2 && $teach->status==2 && !$teach->photos){
                $name = $teach->name .' '.$teach->last_name;
                $photo = $teach->sex == 0 ? config('app.url').'/logo/teach_default_nan.png' : config('app.url').'/logo/teach_default_nv.png';
            }
            $Im_user = ImUser::where('type',1)->where('user_id',$v->id)->first();
            $res = $this->createImOneAccount(['Identifier'=>config('app.env').'_'.$Im_user->id,'Nick'=>$name,'FaceUrl'=> $photo]);
            echo $res.PHP_EOL;
        }
        echo "全部已完成";
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
