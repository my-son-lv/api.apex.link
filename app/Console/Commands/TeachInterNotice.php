<?php

namespace App\Console\Commands;

use App\Jobs\SendWxNotice;
use App\Models\Companys;
use App\Models\Interview;
use App\Models\Job;
use App\Models\Member;
use App\Models\MemberInfo;
use App\Models\Official;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class TeachInterNotice extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'TeachInterNotice';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '面试提前一小时通知';

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
        $list = Interview::where('status',1)->get();
        foreach ($list as $k => $v){
            if(strtotime($v->inte_time) > time() && (date("Y-m-d H:i",strtotime($v->inte_time)) == date("Y-m-d H:i",strtotime("+1 hours")) ) ){
                $teach = MemberInfo::where('mid',$v->mid)->first();
                $teach_account = Member::find($v->mid);
                $company = Companys::find($v->cid);
                Log::info(json_encode($company));
                if($company->unionid && config('app.env') == 'production'){
                    $officials = Official::where('unionid',$company->unionid)->where('status',1)->first();
                    Log::info(json_encode($officials));
                    if($officials) {
                        $job = Job::find($v->jid);
                        //发送微信通知
                        $wxNoticeData = [
                            'openid' => $officials->openid,
                            'type' => 4,
                            'title' => '您预约的面试将在1个小时候后开始，请在电脑端准时参加面试。',
                            'memo' => '如有疑问请联系您的顾问。',
                            'key' => [
                                'keyword1' => $job->name,
                                'keyword2' => date("Y年m月d日 H:i", strtotime($v->inte_time)),
                                'keyword3' => '--',
                            ],
                        ];
                        dispatch(new SendWxNotice($wxNoticeData));
                    }
                }


                if(config('app.env') == 'production') {
                    //获取运营部通知手机好
                    $phones = $this->getYunYingUserPhone();
                    //获取通知内容
                    $Feishu['company_name'] = $company->company_name;
                    $Feishu['teach_name'] = $teach->name.' '.$teach->last_name;
                    $Feishu['time'] = date("Y年m月d日 H:i",strtotime($v->inte_time));
                    $this->FeiShuSendText($phones,returnFeiShuMsg(7 ,$Feishu));
                }
                $emailData['time'] = date("H:i Y/m/d",strtotime($v->inte_time));
                $emailData['teach_name'] = $teach->name . ' ' .$teach->last_name;
                $emailData['company_name'] = $company->company_name;
                $email = $teach_account->email;
                Mail::send('email.mianshi_shenyu',['emailData' => $emailData],function($message)use($email){
                    $message ->to($email)->subject('寰球阿帕斯');
                });
            }
        }
    }

    /**
     * 获取要通知的运营部人员手机号
     * @param int[] $user [7,14,17]李亚静 周婷婷 安奎
     * @return mixed 返回手机号['13521339391','...']
     */
    public function getYunYingUserPhone($user  = [1]){
        return User::whereIn('id',$user)->pluck('phone');
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
//                    Log::info('发送信息失败:'.json_encode($user_send));
                    return false;
                }
            }else{
//                Log::info('获取用户信息失败:'.json_encode($user_list));
                return false;
            }
        }else{
//            Log::info('获取token失败:'.json_encode($user));
            return false;
        }
    }
}
