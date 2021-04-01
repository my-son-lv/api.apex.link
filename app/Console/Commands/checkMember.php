<?php

namespace App\Console\Commands;

use App\Models\Event;
use App\Models\Files;
use App\Models\ImUser;
use App\Models\Member;
use App\Models\MemberAdviser;
use App\Models\MemberInfo;
use App\Models\MemberInfoChecked;
use App\Models\Notice;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Tencent;

class checkMember extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'checkMember';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'checkMember';

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
        ###修改为待审核外交为已审核
        $list = MemberInfoChecked::where('status',1)->get();
        foreach ($list as $k => $v){
            if($v->submit_time && (abs((strtotime($v->submit_time) - time())) / 60 >= 10)   ){
                //开启事物
                DB::beginTransaction();
                $model = MemberInfo::where('mid',$v->mid)->first();
                if(!$model){
                    $model = new MemberInfo();
                }
                $up_1 = MemberInfoChecked::where('id',$v->id)->update(['status' => 2]);
                $member = Member::find($v->mid);
                Event::addEvent($v->name.' '.$v->last_name.'系统 通过了入驻申请',$v->mid);
                Notice::addNotice(returnNoticeMsg(['teach_name' => $v->name.' '.$v->last_name],1009),1,1009);

                $email = $member->email;
                unset($v['id']);
                unset($v['status']);
                unset($v['check_log_id']);
                unset($v['created_at']);
                unset($v['updated_at']);
                unset($v['submit_time']);
                $data1 = $v->toArray();
                foreach ($data1 as $k1 => $v1){
                    $model->$k1 = $v1;
                }
                if($up_1 && $model->save()){
                    //添加顾问为周婷婷
                    $count = MemberAdviser::where('mid',$v->mid)->count();
                    if($count){
                        MemberAdviser::where('mid',$v->mid)->update(['uid'=> 14]);
                    }else{
                        MemberAdviser::create(['uid'=> 14,'mid'=>$v->mid]);
                    }
                    Mail::send('email.checkSuccess',['web_url' => config('app.teach_url')],function($message)use($email){
                        $message ->to($email)->subject('寰球阿帕斯');
                    });
                    //发送消息
                    $imUser = ImUser::where('user_id',$v->mid)->where('type',1)->first();
                    //修改昵称 导入头像
                    $res = $this->createImOneAccount([
                        'Identifier'=>config('app.env').'_'.$imUser->id,
                        'Nick'=>$model->first_name.' '.$model->last_name,
                        'FaceUrl'=> $v->photos ? Files::where('id',$v->photos)->pluck('path')->first() : $v->sex==0 ? $this->getDefaultLogo(4)[0]['path'] : $this->getDefaultLogo(5)[0]['path'] ,
                    ]);
                    $res = json_decode($res,true);
                    if($res['ActionStatus'] != 'OK'){
                        echo "回滚 导入头像失败".PHP_EOL;
                        DB::rollback();
                    }
                    DB::commit();
                }else{
                    DB::rollback();
                    echo "回滚 保存失败了".PHP_EOL;
                }
            }
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

    /**
     * 返回默认头像 1外教 2企业 3运营后台
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
        }
        return $img;
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
