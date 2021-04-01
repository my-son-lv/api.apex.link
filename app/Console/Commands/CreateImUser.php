<?php

namespace App\Console\Commands;

use App\Models\Companys;
use App\Models\Files;
use App\Models\ImUser;
use App\Models\Member;
use App\Models\MemberInfo;
use App\Models\MemberInfoChecked;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Tencent;


class CreateImUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:createImUser';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '统一创建聊天用户';

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
        echo "create Im User Start" . PHP_EOL;
        echo "开启事物>>>>>>>>>>>" . PHP_EOL;
        DB::beginTransaction();
        try {

            echo "开始-->创建运营后台用户" . PHP_EOL;
            //运营后台用户
            $user = User::all();
            foreach ($user as $k => $v) {
                $model = new ImUser();
                $model->user_id = $v->id;
                $model->type = 3;
                if (!$model->save()) {
                    DB::rollback();
                    echo "事物回滚>>>>>>>>>>>" . PHP_EOL;
                    echo "出错了ID:" . $v->id . PHP_EOL;
                } else {
                    $res = $this->createImOneAccount(['Identifier' => config('app.env') . '_' . $model->id, 'Nick' => $v->name, 'FaceUrl' => config('app.url') . '/logo/user_defaut_logo.png']);
                    echo $res . PHP_EOL;
                    $res = json_decode($res, true);
                    if ($res['ActionStatus'] != 'OK') {
                        DB::rollback();
                        Log::info('IM注册失败了,错误码:' . $res['ErrorCode'] . ' 错误描述:' . $res['ErrorInfo']);
                        echo "事物回滚>>>>>>>>>>>" . PHP_EOL;
                        echo "出错了ID:" . $v->id . PHP_EOL;
                    }
                }
            }
            echo "结束-->创建运营后台用户" . PHP_EOL;


            echo "开始-->创建外教用户" . PHP_EOL;
            $list = Member::all();
            foreach ($list as $k => $v) {
                $name = $v->nick_name;
                $photo = config('app.url') . '/logo/teach_default_nan.png';
                //查询是否提交
                $teach = MemberInfoChecked::where('mid', $v->id)->first();
                if ($teach && $teach->status == 2 && $teach->status == 2 && !$teach->photos) {
                    $name = $teach->name . ' ' . $teach->last_name;
                    $photo = $teach->sex == 0 ? config('app.url') . '/logo/teach_default_nan.png' : config('app.url') . '/logo/teach_default_nv.png';
                }
                $model = new ImUser();
                $model->user_id = $v->id;
                $model->type = 1;
                if (!$model->save()) {
                    DB::rollback();
                    echo "事物回滚>>>>>>>>>>>" . PHP_EOL;
                    echo "出错了ID:" . $v->id . PHP_EOL;
                } else {
                    $res = $this->createImOneAccount(['Identifier' => config('app.env') . '_' . $model->id, 'Nick' => $name, 'FaceUrl' => $photo]);
                    echo $res . PHP_EOL;
                    $res = json_decode($res, true);
                    if ($res['ActionStatus'] == 'OK') {
                        //发送消息
                        $imUser = ImUser::where('user_id', 15)->where('type', 3)->first();
                        $res = $this->sendImMsg(
                            config('app.env') . '_' . $imUser->id,
                            config('app.env') . '_' . $model->id,
                            'Your exclusive customer service has been online, you can consult at any time if you have any questions'
                        );
                        echo $res . PHP_EOL;
                        $res = json_decode($res, true);
                        if ($res['ActionStatus'] != 'OK') {
                            DB::rollback();
                            Log::info('IM消息发送失败,错误码:' . $res['ErrorCode'] . ' 错误描述:' . $res['ErrorInfo']);
                            echo "事物回滚>>>>>>>>>>>" . PHP_EOL;
                            echo "出错了ID:" . $v->id . PHP_EOL;
                        }
                    } else {
                        DB::rollback();
                        Log::info('IM注册失败了,错误码:' . $res['ErrorCode'] . ' 错误描述:' . $res['ErrorInfo']);
                        echo "事物回滚>>>>>>>>>>>" . PHP_EOL;
                        echo "出错了ID:" . $v->id . PHP_EOL;
                    }
                }
            }

            echo "结束-->创建外教用户" . PHP_EOL;
            echo "开始-->创建企业用户" . PHP_EOL;
            //企业用户审核通过才能聊天
            $company = Companys::all();//where('status',2)->get();
            foreach ($company as $k => $v) {
                $model = new ImUser();
                $model->user_id = $v->id;
                $model->type = 2;
                if (!$model->save()) {
                    DB::rollBack();
                    echo "事物回滚>>>>>>>>>>>" . PHP_EOL;
                    echo "出错了ID:" . $v->id . PHP_EOL;
                } else {
                    $logo = config('app.url') . '/logo/company_defaut_logo.png';
                    if ($v->logo) {
                        $logo = Files::where('id', $v->logo)->pluck('path')->first();
                    }
                    $v->company_name = $v->company_name ?? '企业用户';
                    $res = $this->createImOneAccount(['Identifier' => config('app.env') . '_' . $model->id, 'Nick' => $v->company_name, 'FaceUrl' => $logo]);
                    echo $res . PHP_EOL;
                    $res = json_decode($res, true);
                    if ($res['ActionStatus'] == 'OK') {
                        //发送消息
                        $imUser = ImUser::where('user_id', 12)->where('type', 3)->first();
                        $sendMsgRes = $this->sendImMsg(
                            config('app.env') . '_' . $imUser->id,
                            config('app.env') . '_' . $model->id,
                            'hi，欢迎来到寰球阿帕斯，我是客服小寰，任何问题都可以和我联系哦'
                        );
                        echo $sendMsgRes . PHP_EOL;
                        $sendMsgRes = json_decode($sendMsgRes, true);
                        if ($sendMsgRes['ActionStatus'] != 'OK') {
                            DB::rollback();
                            Log::info('IM消息发送失败,错误码:' . $res['ErrorCode'] . ' 错误描述:' . $res['ErrorInfo']);
                            echo "事物回滚>>>>>>>>>>>" . PHP_EOL;
                            echo "出错了ID:" . $v->id . PHP_EOL;
                        }
                    } else {
                        DB::rollback();
                        Log::info('IM注册失败了,错误码:' . $res['ErrorCode'] . ' 错误描述:' . $res['ErrorInfo']);
                        echo "事物回滚>>>>>>>>>>>" . PHP_EOL;
                        echo "出错了ID:" . $v->id . PHP_EOL;
                    }
                }
            }
            echo "结束-->创建企业用户" . PHP_EOL;


            DB::commit();
            echo "提交事物>>>>>>>>>>>" . PHP_EOL;
            echo "创建用户成功,共创建用户:" . ImUser::count() . PHP_EOL;
        } catch (\Exception $e) {
            DB::rollBack();
            echo "事物回滚>>>>>>>>>>>" . PHP_EOL;
            echo "出错了:" . $e->getMessage();
        }
    }


    /**
     * 腾讯IM导入单个账号
     */
    public function createImOneAccount($user)
    {
        /*$user = ['Identifier'=>'justin_test_123','Nick'=>'Justin33','FaceUrl'=> ''];*/
        $url = $this->settingUrl('v4/im_open_login_svc/account_import');
        return $this->postCurl($url, $user);
    }


    public function sendImMsg($from_user, $to_user, $text)
    {
        $msg = [
            'SyncOtherMachine' => 2,
            'From_Account' => $from_user,
            'To_Account' => $to_user,
//            'MsgLifeTime'       => 10, 默认7天
            'MsgRandom' => rand(111111, 999999999),
            'MsgTimeStamp' => time(),
            'MsgBody' => [[
                'MsgType' => 'TIMTextElem',
                'MsgContent' => [
                    'Text' => $text,
                ]
            ]]
        ];
        $url = $this->settingUrl('v4/openim/sendmsg');
        return $this->postCurl($url, $msg);
    }


    /**
     * CURL Post发送数据
     *
     * @param $url 地址
     * @param $option 参数数据
     * @param $header 消息头
     * @param $type 发送方式
     */
    public function postCurl($url, $option, $header = 0, $type = 'POST')
    {
        $curl = curl_init(); // 启动一个CURL会话
        curl_setopt($curl, CURLOPT_URL, $url); // 要访问的地址
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE); // 对认证证书来源的检查
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE); // 从证书中检查SSL加密算法是否存在
        curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.0; Trident/4.0)'); // 模拟用户使用的浏览器
        if (!empty ($option)) {
            $options = json_encode($option);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $options); // Post提交的数据包
        }
        curl_setopt($curl, CURLOPT_TIMEOUT, 30); // 设置超时限制防止死循环
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); // 获取的信息以文件流的形式返回
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $type);
        $result = curl_exec($curl); // 执行操作
        curl_close($curl); // 关闭CURL会话
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
    public function settingUrl($apiUrl)
    {
        $api = new Tencent\TLSSigAPIv2(config('videos.SDK_APP_ID'), config('videos.USER_SIG'));
        $sig = $api->genSig(config('videos.SDK_ADMIN_USER'));
        $url = config('videos.IM_SERVICE_URL');
        $url .= $apiUrl;
        $url .= '?' . 'sdkappid=' . config('videos.SDK_APP_ID');
        $url .= '&' . 'identifier=' . config('videos.SDK_ADMIN_USER');
        $url .= '&' . 'usersig=' . $sig;
        $url .= '&' . 'random=' . rand(111111, 999999999);
        $url .= '&contenttype=json';
        return $url;
    }
}
