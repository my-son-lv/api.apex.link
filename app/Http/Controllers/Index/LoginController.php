<?php

namespace App\Http\Controllers\Index;

use App\Models\Code;
use App\Models\Collect;
use App\Models\CompanyCheckLog;
use App\Models\Companys;
use App\Models\Down;
use App\Models\Event;
use App\Models\Files;
use App\Models\ImUser;
use App\Models\Interview;
use App\Models\Job;
use App\Models\JobApplication;
use App\Models\Member;
use App\Models\MemberInfo;
use App\Models\MemberInfoChecked;
use App\Models\MemberInfoCheckedLog;
use App\Models\Notice;
use App\Models\Vip;
use App\Models\VipAction;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Log;
use Mrgoon\AliSms\AliSms;
use Tencent;
use Illuminate\Support\Facades\Mail;

class LoginController extends Controller
{

    private $mail;
    public function __construct(Mail $mail)
    {
        $this->mail = $mail;
    }

    /**
     * 公司密码找回
     * @param Request $request
     * @return LoginController
     */
    public function companyRestPassword(Request $request){
        $phone      = $request->get('phone','');
        $code       = $request->get('code','');
        $password   = $request->get('password','');

        if(!$phone || !$code || !$password){
            return $this->fail('100001');
        }
        $codeData = Code::where('email',$phone)->orderBy('id','desc')->first();
        if(!$codeData){
            return $this->fail('100005');
        }
        if(strtotime($codeData->created_at)+300 < time()){
            return $this->fail('100006');
        }
        if($code != $codeData->code){
            return $this->fail('100002');
        }

        $model = Companys::where('phone',$phone)->first();
        if(!$model){
            return $this->fail(100004);
        }
        $model->password    = md5(md5($password));
        Code::delCode($phone);
        if($model->save()){
            return $this->success();
        }else{
            return $this->fail();
        }
    }

    /**
     * 企业用户登出
     * @return LoginController
     */
    public function logoutCompany(){
        $token  = Input::get('token','');
        $company = Companys::where('token',$token)->first();
        if(!$company){
            return $this->fail(1000202);
        }else{
            $company->token              = null;
            $company->token_expire_time  = null;
            if($company->save()){
                return $this->success();
            }else{
                return $this->fail(100000);
            }
        }
    }

    /**
     * 通过token获取用户信息
     * @param Request $request
     * @return LoginController
     */
    public function getInfoByToken(Request $request){
        $token =  $request->get('token');
        $company = Companys::where('token',$token)->first();
        if(!$company){
            return $this->fail(1000201);
        }
        $is_read = 0;
        $msg     = '';
        if($company->check_log_id){
            $check = CompanyCheckLog::find($company->check_log_id);
            $is_read = $check->is_read;
            $msg     = $check->info;
        }else{
            if($company->status == 2){
                $is_read = 2;
            }
        }
        $imUser = ImUser::where('type',2)->where('user_id',$company->id)->first();
        $im_user_id = '';
        $sig = '';
        if($imUser){
            $im_user_id = config('app.env').'_'.$imUser->id;
            $api = new Tencent\TLSSigAPIv2(config('videos.SDK_APP_ID'),config('videos.USER_SIG'));
            $sig = $api->genSig($im_user_id);
        }
        //查询以后的待面试和面试中的取最近的一条
        $interData = null;
//        $interview = Interview::where('cid',$company->id)->where('inte_time','>',date("Y-m-d H:i:s"))->whereIn('status',[1,2])->orderBy('inte_time','desc')->first();
        $interview = Interview::where('cid',$company->id)->where(function ($query){
            $query->where(function ($q1){
                $q1->where('status',1)->where('inte_time','>',date("Y-m-d H:i:s"));
            });
            $query->OrWhere('status',2);
        })->orderBy('inte_time','asc')->first();
        if($interview){
            $member = MemberInfo::where('mid',$interview->mid)->first();
            if($member){
                $interData['inte_time'] = $interview->inte_time;
                $interData['first_name'] = $member->name;
                $interData['id'] = $interview->id;
                $interData['last_name'] = $member->last_name;
                $interData['status'] = $interview->status;
                $interData['mid'] = $interview->mid;
            }
        }
        $job_num = 1;
        if($company->vip_actions_id){
            $action = VipAction::find($company->vip_actions_id);
            $vip = Vip::find($action->vip_id);
            $job_num = $job_num+$vip->job_num;
        }
        return $this->success([
            'phone'     =>  $company->phone,
            'status'    =>  $company->status,
            'company_name'   =>  $company->company_name,
            'token'     =>  $token,
            'is_read'   =>  $is_read,
            'info' => $msg ? $msg : '',
            'logo'      =>  $company->logo,
            'logo_path' =>  $company->logo ? Files::whereIn('id',explode(',',$company->logo))->get() : $this->getDefaultLogo(2),
            'im_user_id'=>  $im_user_id,
            'im_user_sig' => $sig,
            'submit_flg'  => $company->submit_num ? 1:0,
            'job_flg'   => Job::where('cid',$company->id)->count(),
            'interview'    => $interData,
            'code'      => $company->code,
            'vip'       => $company->vip_actions_id ? 1 : 0,
            'vip_exp_time'   => $company->vip_actions_id ? VipAction::where('id',$company->vip_actions_id)->value('end_time') : null,
            'collect_num'       => Collect::where('cid',$company->id)->count(),
            'yy_job_num'   => Job::where('cid',$company->id)->where('flg',1)->count(),
            'job_num'   =>  $job_num,
            'down_num'  => Down::where('cid',$company->id)->count(),
            'job_application' => JobApplication::from('job_applications as a')->Join('job as b','a.jid','=','b.id')->where('a.cid',$company->id)->count(),
            'year_xlh'       => Cache::has('year_xlh')  ? Cache::get('year_xlh') : '',
        ]);
    }

    /**
     * 公司登陆
     * @param Request $request
     * @return LoginController
     */
    public function companyLogin(Request $request){
        $phone      = Input::get('phone','');
        $password   = Input::get('password','');
        $type       = Input::get('type',1);//1密码登录 2验证码登录
        $code       = Input::get('code','');//短信验证码
        $wx_code    = $request->get('wx_code','');

        if($type ==1 || $type ==2 ) {
            if (!$phone) {
                return $this->fail('100001');
            }
            $company = Companys::where('phone', $phone)->first();
            if (!$company) {
                return $this->fail(100004);
            }
            if ($type == 1) {
                if (!$password) {
                    return $this->fail('100001');
                }
                if ($company->password != md5(md5($password))) {
                    return $this->fail(100008);
                }

            } else {
                if (!$code) {
                    return $this->fail('100001');
                }
                $codeData = Code::where('email', $phone)->orderBy('id', 'desc')->first();
                if (!$codeData) {
                    return $this->fail('100005');
                }
                if (strtotime($codeData->created_at) + 300 < time()) {
                    return $this->fail('100006');
                }
                if ($code != $codeData->code) {
                    return $this->fail('100002');
                }
                Code::delCode($phone);
            }
            $is_read = 0;
            $msg     = '';
            $mode = Companys::where('phone', $phone)->first();
            $token = crateToken($company->id);
            $mode->last_login_ip = $request->getClientIp();
            $mode->last_login_time = date("Y-m-d H:i:s");
            $mode->token = $token;
            $mode->token_expire_time = date("Y-m-d H:i:s", strtotime("+30 day"));
            //解密open_id
            Log::info('小程序CODE:'.json_encode($wx_code));
            $miniProgram = \EasyWeChat::miniProgram();
            $wx_data = $miniProgram->auth->session($wx_code);
            Log::info('小程序CODE解密:'.json_encode($wx_data));
            $is_wx = 0;
            if(!isset($wx_data['errcode'])){
                $companyOpenIdCount = Companys::where('open_id',$wx_data['openid'])->count();
                if(!$mode->open_id){
                    if(!$companyOpenIdCount){
                        $mode->open_id      = $wx_data['openid'];
                        $mode->session_key  = $wx_data['session_key'];
                        isset($wx_data['unionid']) && $mode->unionid = $wx_data['unionid'];
                    }else{
                        $is_wx = 1;//更换标识
                    }
                }else{
                    if($mode->open_id!=$wx_data['openid']){
                        $is_wx = 1;//更换标识
                    }else{
                        $mode->session_key  = $wx_data['session_key'];
                    }
                }
            }
            if ($mode->save()) {
                if ($company->check_log_id) {
                    $check = CompanyCheckLog::find($company->check_log_id);
                    $is_read = $check->is_read;
                    $msg = $check->info;
                }else{
                    if($company->status == 2){
                        $is_read = 2;
                    }
                }
                $imUser = ImUser::where('type', 2)->where('user_id', $company->id)->first();
                $im_user_id = '';
                $sig = '';
                if ($imUser) {
                    $im_user_id = config('app.env') . '_' . $imUser->id;
                    $api = new Tencent\TLSSigAPIv2(config('videos.SDK_APP_ID'), config('videos.USER_SIG'));
                    $sig = $api->genSig($im_user_id);
                }
                //查询以后的待面试和面试中的取最近的一条
                $interData = null;
//                $interview = Interview::where('cid',$company->id)->where('inte_time','>',date("Y-m-d H:i:s"))->whereIn('status',[1,2])->orderBy('inte_time','desc')->first();
                $interview = Interview::where('cid',$company->id)->where(function ($query){
                    $query->where(function ($q1){
                        $q1->where('status',1)->where('inte_time','>',date("Y-m-d H:i:s"));
                    });
                    $query->OrWhere('status',2);
                })->orderBy('inte_time','asc')->first();

                if($interview){
                    $member = MemberInfo::where('mid',$interview->mid)->first();
                    if($member){
                        $interData['inte_time'] = $interview->inte_time;
                        $interData['first_name'] = $member->name;
                        $interData['id'] = $interview->id;
                        $interData['last_name'] = $member->last_name;
                        $interData['status'] = $interview->status;
                        $interData['mid'] = $interview->mid;
                    }
                }
                $job_num = 1;
                if($company->vip_actions_id){
                    $action = VipAction::find($company->vip_actions_id);
                    $vip = Vip::find($action->vip_id);
                    $job_num = $job_num+$vip->job_num;
                }
                return $this->success([
                    'phone' => $company->phone,
                    'status' => $company->status,
                    'company_name' => $company->company_name,
                    'token' => $token,
                    'is_read' => $is_read,
                    'info' => $msg ? $msg : '',
                    'logo' => $company->logo,
                    'logo_path' =>  $company->logo ? Files::whereIn('id',explode(',',$company->logo))->get() : $this->getDefaultLogo(2),
                    'im_user_id' => $im_user_id,
                    'im_user_sig' => $sig,
                    'submit_flg'  => $company->submit_num ? 1:0,
                    'job_flg'   => Job::where('cid',$company->id)->count(),
                    'interview'    => $interData,
                    'code'      => $company->code,
                    'vip'       => $company->vip_actions_id ? 1 : 0,
                    'vip_exp_time'   => $company->vip_actions_id ? VipAction::where('id',$company->vip_actions_id)->value('end_time') : null,
                    'collect_num'       => Collect::where('cid',$company->id)->count(),
                    'yy_job_num'   => Job::where('cid',$company->id)->where('flg',1)->count(),
                    'job_num'   =>  $job_num,
                    'down_num'  => Down::where('cid',$company->id)->count(),
                    'job_application' => JobApplication::from('job_applications as a')->rightJoin('job as b','a.jid','=','b.id')->where('a.cid',$company->id)->count(),
                    'is_wx'         => $is_wx,//0正常 1更换绑定
                    'year_xlh'       => Cache::has('year_xlh')  ? Cache::get('year_xlh') : '',//config('app.year_xlh'),
                ]);
            } else {
                return $this->fail();
            }
        }
    }

    /**
     * 公司注册
     * @param Request $request
     * @return LoginController
     */
    public function companyRegister(Request $request){
        $phone      = $request->get('phone','');
        $code       = $request->get('code','');
        $password   = $request->get('password','');
        $invite_code = $request->get('invite_code','');
//        $wx_code     = $request->get('wx_code','');

        if(!$phone || !$password){
            return $this->fail('100001');
        }
        $codeData = Code::where('email',$phone)->orderBy('id','desc')->first();
        if(!$codeData){
            return $this->fail('100005');
        }
        if(strtotime($codeData->created_at)+300 < time()){
            return $this->fail('100006');
        }
        if($code != $codeData->code){
            return $this->fail('100002');
        }

        $flg = Companys::where('phone',$phone)->first();
        if($flg){
            return $this->fail(100003);
        }


        DB::beginTransaction();
        $model = new Companys();
        $model->phone       = $phone;
        //解密open_id
//        $miniProgram = \EasyWeChat::miniProgram();
//        $wx_data = $miniProgram->auth->session($wx_code);
//        if(!isset($wx_data['errcode'])){
//            $companyOpenIdCount = Companys::where('open_id',$wx_data['openid'])->count();
//            if(!$companyOpenIdCount){
//                $model->open_id      = $wx_data['openid'];
//                $model->session_key  = $wx_data['session_key'];
//            }
//        }
        $model->password    = md5(md5($password));
        $model->register_ip = $request->getClientIp();
        $model->register_time= date("Y-m-d H:i:s");
        $model->code = makeCouponCard();
        $invite_code && $model->invite_code = $invite_code;
        Code::delCode($phone);
        if($model->save()){
            Event::addEvent(' 注册了平台',$model->id,2);
            Notice::addNotice(returnNoticeMsg(['company_name' => $phone],1002),1,1002);
            $model1 = new ImUser();
            $model1->user_id = $model->id;
            $model1->type = 2;
            if(!$model1->save()){
                DB::rollback();
                Log::info('企业用户IM表注册失败了');
                return $this->fail();
            }else {
                //注册IM同时客服发送消息给企业客户
                $logo = config('app.url') . '/logo/company_defaut_logo.png';
                $res = $this->createImOneAccount(['Identifier' => config('app.env') . '_' . $model1->id, 'Nick' => '企业用户', 'FaceUrl' => $logo]);
                $res = json_decode($res, true);
                if ($res['ActionStatus'] == 'OK') {
                    //发送消息
                    $imUser = ImUser::where('user_id', 12)->where('type', 3)->first();
                    $sendMsgRes = $this->sendImMsg(
                        config('app.env') . '_' . $imUser->id,
                        config('app.env') . '_' . $model1->id,
                        '欢迎入驻寰球阿帕斯外教招聘平台，很高兴为您服务。'
                    );
                    $sendMsgRes = json_decode($sendMsgRes, true);
                    if ($sendMsgRes['ActionStatus'] != 'OK') {
                        DB::rollback();
                        Log::info('IM消息发送失败,错误码:' . $res['ErrorCode'] . ' 错误描述:' . $res['ErrorInfo']);
                        echo "事物回滚>>>>>>>>>>>" . PHP_EOL;
                        echo "出错了ID:" . $model1->id . PHP_EOL;
                        return $this->fail();
                    }
                    DB::commit();
                    return $this->success();
                } else {
                    DB::rollback();
                    Log::info('IM注册失败了,错误码:' . $res['ErrorCode'] . ' 错误描述:' . $res['ErrorInfo']);
                    return $this->fail();
                }
            }
        }else{
            DB::rollback();
            Log::info('企业用户注册失败了');
            return $this->fail();
        }
    }

    //图形验证码
    public function captcha()
    {
        return $this->success([ app('captcha')->create('default', true)]);
    }

    /**
     * 发送短信验证码
     * @param Request $request
     * @return CompanyController
     */
    public function sendSms(Request $request){
        $phone  = $request->get('phone','');
        $key    = $request->get('key','');
        $img_code   = $request->get('code','');
        if(!$phone){
            return $this->fail(100001);
        }
        if($key && $img_code){
            if (!captcha_api_check($img_code, $key)){
                return $this->fail(2000006);
            }
        }
        $code = mt_rand(100000,999999);
        $data = Code::where('email',$phone)->orderBy('id','desc')->first();
        if($data && strtotime($data->created_at)+60 > time()){
            return $this->fail(100007);
        }
        try{
            DB::beginTransaction();
            /*if(Code::addCode($phone,$code) && $this->aliyunSendSms($phone,'SMS_205315358',$code)){
                DB::commit();
                return $this->success();
            }else{
                DB::rollback();
                return $this->fail();
            }*/
            $sms_send_flg = $this->aliyunSendSms($phone,'SMS_205315358',$code);
            Code::addCode($phone,$code);
            if($sms_send_flg['status'] == true){
                DB::commit();
                return $this->success();
            }else{
                DB::rollback();
                return $this->fail(100000,$sms_send_flg['msg']);
            }
        }catch (\Exception $exception){
            DB::rollback();
            Log::info($exception->getMessage());
            return $this->fail();
        }
    }

    /**
     * 通过token获取状态
     * @return LoginController
     */
    public function getStatusByToken(){
        $token = Input::get('token','');
        $member = Member::where('token',$token)->first();
        return  $this->loginGetMemberInfo($member);
    }

    /**
     * 外教用户登录
     * @param Request $request
     * @return LoginController
     */
    public function login(Request $request){
        $email      = Input::get('email','');
        $password   = Input::get('password','');
        $token = Input::get('token','');
        if(!$email){
            return $this->fail('100001');
        }
        if(!$password){
            return $this->fail('100001');
        }

        $member = Member::where('email',$email)->first();
        if(!$member){
            return $this->fail(100004);
        }

        if($member->password == md5(md5($password))){
            $token = crateToken($member->id);
            $member->last_login_ip = $request->getClientIp();
            $member->last_login_time = date("Y-m-d H:i:s");
            $member->token           = $token;
            $member->token_expire_time = date("Y-m-d H:i:s",strtotime("+30 day"));
            $member->save();
            $member_check = MemberInfoChecked::where('mid',$member->id)->first();
            if(!$member_check){
                MemberInfoChecked::create(['mid' => $member->id]);
            }
            return  $this->loginGetMemberInfo($member);
        }else{
            return $this->fail(100008);
        }
    }

    /**
     * 外教登录获取信息
     * @param $member
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    protected function loginGetMemberInfo($member){
        $info = MemberInfoChecked::where('mid',$member->id)->first();
        $is_read = 0;
        $status = 0;
        $msg = null;
        $notes_file = [];
        $submit_time = null;
        if($info){
            $submit_time = $info->submit_time ? strtotime($info->submit_time) : null;
            if($info->check_log_id){
                $checkLog = MemberInfoCheckedLog::find($info->check_log_id);
                $msg = $checkLog->info;
            }
            $status = $info->status;
            if($status == 2 || $status == 3){
                $data = MemberInfoCheckedLog::find($info->check_log_id);
                if($data){
                    $is_read = $data->flg;
                }else{
                    if($status == 2){
                        $is_read = 2;
                    }
                }
            }
            if($info->notes){
                $notes_file = Files::whereIn('id',explode(',',$info->notes))->get();
            }
        }
        $imUser = ImUser::where('type',1)->where('user_id',$member->id)->first();
        $im_user_id = config('app.env').'_'.$imUser->id;
        $api = new Tencent\TLSSigAPIv2(config('videos.SDK_APP_ID'),config('videos.USER_SIG'));
        $sig = $api->genSig($im_user_id);
        return $this->success([
            'is_read'   =>  $is_read,
            'status'    =>  $status,
            'nick_name' =>  $member->nick_name,
            'user_id'   => $member->user_id,
            'sign_id'   => $member->sign_id,
            'info'      =>  $msg,
            'token'     =>  $member->token,
            'notes_file'=>$notes_file,
            'email'     =>  $member->email,
            'name'      =>  $info ? $info->name :'',
            'im_user_id'=>  $im_user_id,
            'im_user_sig' => $sig,
            'submit_time' => $submit_time,
        ]);

    }

    /**
     * 用户登出
     * @return LoginController
     */
    public function logout(){
        $token  = Input::get('token','');
        $member = Member::where('token',$token)->first();
        if(!$member){
            return $this->fail(1000202);
        }else{
            $member->token              = null;
            $member->token_expire_time  = null;
            if($member->save()){
                return $this->success();
            }else{
                return $this->fail(100000);
            }
        }
    }


    /**
     * 判断用户是否已存在
     * @return MemberController
     */
    public function isMemberExist(){
        $email = Input::get('email');
        if(!$email){
            return $this->fail('100001');
        }
        if(Member::isExist($email)){
            return $this->success(['flg' => true]);
        }else{
            return $this->success(['flg' => false]);
        }
    }

    /**
     * 发送验证码
     * @return MemberController
     */
    public function sendCode(){
        $email = Input::get('email');
        if(!$email){
            return $this->fail('100001');
        }
        $code = mt_rand(100000,999999);
        $data = Code::where('email',$email)->orderBy('id','desc')->first();
        if($data && strtotime($data->created_at)+60 > time()){
            return $this->fail(100007);
        }
        try{
            if(Code::addCode($email,$code)){
                $this->mail::send('email.sendCode',['code' => $code],function($message)use($email){
                    $message ->to($email)->subject('寰球阿帕斯-验证码');
                });
                return $this->success();
            }else{
                return $this->fail();
            }
        }catch (\Exception $exception){
            Log::info($exception->getMessage());
            return $this->fail();
        }

    }

    /**
     * 注册接口
     * @return MemberController
     */
    public function register(Request $request){
        $nick_name  = Input::get('nick_name','');
        $email      = Input::get('email','');
        $code       = Input::get('code','');
        $password   = Input::get('password','');
        $invite_code = Input::get('invite_code','');
        if(!$nick_name){
            return $this->fail('100001');
        }
        if(!$email){
            return $this->fail('100001');
        }
        if(!$code){
            return $this->fail('100001');
        }
        if(!$password){
            return $this->fail('100001');
        }
        $codeData = Code::where('email',$email)->orderBy('id','desc')->first();
        if(!$codeData){
            return $this->fail('100005');
        }
        if(strtotime($codeData->created_at)+300 < time()){
            return $this->fail('100006');
        }
        if($code != $codeData->code){
            return $this->fail('100002');
        }

        if(Member::isExist($email)){
            return $this->fail('100003');
        }

        $model = new Member();
        $model->email       = $email;
        $model->password    = md5(md5($password));
        $model->nick_name   = $nick_name;
        $model->user_id     = $this->genRequestSn(rand(100000,999999));
        $model->sign_id     = $this->genRequestSn(rand(1000000,9999999));
        $model->register_ip = $request->getClientIp();
        $model->register_time= date("Y-m-d H:i:s");
        if($invite_code){
            $model->invite_code = $invite_code;
        }
        DB::beginTransaction();
        Code::delCode($email);
        if($model->save()){
            Event::addEvent('注册了平台',$model->id);
            Notice::addNotice(returnNoticeMsg(['teach_name' => $nick_name],1001),1,1001);
            $model2 = new MemberInfoChecked();
            $model2->mid = $model->id;
            if(!$model2->save()){
                Log::info('审核表注册失败');
                return $this->fail();
            }
            $model1 = new ImUser();
            $model1->type = 1;
            $model1->user_id = $model->id;
            if($model1->save()){
                $res = $this->createImOneAccount(['Identifier'=>config('app.env').'_'.$model1->id,'Nick'=>$model->nick_name,'FaceUrl'=> $this->getDefaultLogo(4)[0]['path']]);
                $res = json_decode($res,true);
                if($res['ActionStatus'] == 'OK'){
                    //发送消息
                    $imUser = ImUser::where('user_id',15)->where('type',3)->first();
                    $sendMsgRes = $this->sendImMsg(
                        config('app.env').'_'.$imUser->id,
                        config('app.env').'_'.$model1->id,
                        'Welcome to Apex Global, find a fit job and right employer.'
                    );
                    $sendMsgRes = json_decode($sendMsgRes,true);
                    if($sendMsgRes['ActionStatus']!='OK'){
                        DB::rollback();
                        Log::info('IM消息发送失败,错误码:'.$res['ErrorCode'].' 错误描述:'.$res['ErrorInfo']);
                        return $this->fail();
                    }else{
                        $token = crateToken($model->id);
                        $model->last_login_ip = $request->getClientIp();
                        $model->last_login_time = date("Y-m-d H:i:s");
                        $model->token           = $token;
                        $model->token_expire_time = date("Y-m-d H:i:s",strtotime("+30 day"));
                        $model->save();
                        DB::commit();
                        return $this->loginGetMemberInfo($model);
                    }
                }else{
                    DB::rollback();
                    Log::info('IM注册失败了,错误码:'.$res['ErrorCode'].' 错误描述:'.$res['ErrorInfo']);
                    return $this->fail();
                }
            }else{
                DB::rollback();
                Log::info('外教用户注册失败了1');
                return $this->fail();
            }
        }else{
            DB::rollback();
            Log::info('外教用户注册失败了');
            return $this->fail();
        }
    }

    /**
     * 忘记密码
     * @return MemberController
     */
    public function forgetPassword(){
        $email      = Input::get('email','');
        $code       = Input::get('code','');
        $password   = Input::get('password','');
        if(!$email){
            return $this->fail('100001');
        }
        if(!$code){
            return $this->fail('100001');
        }
        if(!$password){
            return $this->fail('100001');
        }
        $codeData = Code::where('email',$email)->orderBy('id','desc')->first();
        if(!$codeData){
            return $this->fail('100005');
        }
        if(strtotime($codeData->created_at)+300 < time()){
            return $this->fail('100006');
        }
        if($code != $codeData->code){
            return $this->fail('100002');
        }

        if(!Member::isExist($email)){
            return $this->fail('100004');
        }

        $model =  Member::where('email',$email)->first();
        $model->email       = $email;
        $model->password    = md5(md5($password));
        Code::delCode($email);
        if($model->save()){
            return $this->success();
        }else{
            return $this->fail();
        }
    }
}
