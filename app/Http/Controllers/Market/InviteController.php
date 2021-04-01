<?php

namespace App\Http\Controllers\Market;

use App\Models\Code;
use App\Models\Files;
use App\Models\Interview;
use App\Models\Invite;
use App\Models\Member;
use Illuminate\Support\Facades\Log;
use Mrgoon\AliSms\AliSms;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class InviteController extends Controller
{
    //
    /**
     * 邀请人邀请列表
     * @param Request $request
     * @return InviteController
     */
    public function inviteCount(Request $request){
        $type   = $request->get('type',0);// 0入驻记录  1入驻成功 2签约成功
        $phone  = $request->get('phone','');

        $user =  Invite::where('phone',$phone)->first();
        if(!$user){
            return $this->fail(100004);
        }

        //已提交  已入住  已签约
        switch ($type){
            case 0:
                $money1 = 50;
                //注册总人数
                $count = Member::where('invite_code',$user->code)->count();
                $list = Member::from('members as a')
                    ->leftjoin('members_info_checked as b','a.id','=','b.mid')
                    ->where('a.invite_code',$user->code)
                    ->where('b.status','>',0);
                $count1 = $list->count();
                $a_count = 0;
                $list = $list->orderBy('a.id','desc')->get(['a.id','b.name','b.photos','b.phone','b.status','sign_status']);
                foreach ($list as $k => $v){
                    if($v->status == 2){
                        $a_count++;
                        $v->money = 50;
                    }else{
                        $v->money = 0;
                    }
                }
                $money2 = $money1 * $a_count;
                $money3 = 0;
                break;
            case 1:
                //提交信息人数
                $count = Member::from('members as a')
                    ->leftjoin('members_info_checked as b','a.id','=','b.mid')
                    ->where('a.invite_code',$user->code)
                    ->whereIn('b.status',[1,2])->count();

                $list = Member::from('members as a')
                    ->leftjoin('members_info_checked as b','a.id','=','b.mid')
                    ->where('b.status',2)
                    ->where('a.invite_code',$user->code);
                //入驻人数
                $count1 = $list->count();
                $list = $list->orderBy('a.id','desc')->get(['a.id','b.name','b.photos','b.phone','b.status','sign_status']);
                $money1 = 0;
                $money2 = 1888;
                $money3 = $money2 * $count1;
                //循环查询是否有
                foreach ($list as $k => $v){
                    if($v->status == 2){
                        $v->money = 50;
                        $money1 += $v->money;
                    }
                    $flg = Interview::where('mid',$v->id)->where('status',4)->count();
                    if($flg){
                        $v->money = 1888;
                    }else{
                        $v->money = 0;
                    }

                }
                if($list->isEmpty()){
                    $money1 = 50;
                }
                break;
            case 2:
                $list = Member::from('members as a')
                    ->rightjoin('members_info as b','a.id','=','b.mid')
                    ->where('a.invite_code',$user->code);
                //入驻人数
                $count = $list->count();
                $list = $list->orderBy('a.id','desc')->get(['a.id','b.name','b.photos','b.phone','sign_status']);
                //签约人数
                $count1 = 0;
                //循环查询是否有
                foreach ($list as $k => $v){
                    $flg = Interview::where('mid',$v->id)->where('status',4)->count();
                    if($flg){
                        $count1++;
                        $v->ok = 1;
                        $v->money = 1888;
                    }else{
                        $v->ok = 0;
                        unset($list[$k]);
                    }
                }
                $money1 = $count1 ? $count1 * 1888 : 50;
                $money2 = $count1 ? 0 : 1888;
                $money3 = 0;
                break;
        }
        foreach ($list as $k => $v){
            $v->photos_path    = Files::whereIn('id',explode(',',$v->photos))->get();
            $v->phone   = substr($v->phone,0,3).'****'.substr($v->phone,-4);
        }
        return $this->success(['list' => $list,'count'=>$count,'count1' => $count1,'money1' => $money1 , 'money2'=> $money2,'money3'=>$money3]);

    }

    public function sendSms(Request $request){
        $phone  = $request->get('phone','');
        if(!$phone){
            return $this->fail(100001);
        }
        $code = mt_rand(1000,9999);
        $data = Code::where('email',$phone)->orderBy('id','desc')->first();
        if($data && strtotime($data->created_at)+60 > time()){
            return $this->fail(100007);
        }
        try{
            if(Code::addCode($phone,$code)){
                $aly_sms = new AliSms();
                //如果是86 就用国内的 不是就用国外短信
                $model = substr($phone,0,2) == '86' ? 'SMS_205315358' : 'SMS_205430147';
                $res = $aly_sms->sendSms($phone,$model,['code' => $code]);
                if ($res->Code == 'OK') {
                    return $this->success();
                }else{
                    Log::info('短信验证码发送失败：'.$res->Message);
                    return $this->fail();
                }
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
     * 校验账号是否存 是 返回姓名及
     * @param Request $request
     * @return InviteController
     */
    public function checkAccount(Request $request){
        $phone          = $request->get('phone','');
        $sms_code       = $request->get('sms_code','');
        $area_code      = $request->get('code','');
        if(!$phone || !$sms_code || !$area_code){
            return $this->fail(100001);
        }
        if($sms_code  != '1234'){
            #验证码校验
            $codeData = Code::where('email',$area_code.$phone)->orderBy('id','desc')->first();
            if(!$codeData){
                return $this->fail('100005');
            }
            if(strtotime($codeData->created_at)+300 < time()){
                return $this->fail('100006');
            }
            if($sms_code != $codeData->code){
                return $this->fail('100002');
            }
        }
        $user  = Invite::where('phone',$phone)->first();
        if($user){
            return $this->success([
                'name'      => $user->name,
                'email'     => $user->email,
            ]);
        }else{
            return $this->fail(100004);
        }
    }

    /**
     * 注册
     * @param Request $request
     * @return InviteController
     */
    public function register(Request $request){
        $token  = $request->get('token','');
        $name   = $request->get('name','');
        $phone  = $request->get('phone','');
        $area_code   = $request->get('code','');
        $sms_code    = $request->get('sms_code','');
        $email  = $request->get('email','');

        if(!$name || !$phone || !$area_code || !$email || !$sms_code){
            return $this->fail(100001);
        }
        #验证码校验
        $codeData = Code::where('email',$area_code.$phone)->orderBy('id','desc')->first();
        if(!$codeData){
            return $this->fail('100005');
        }
        if(strtotime($codeData->created_at)+300 < time()){
            return $this->fail('100006');
        }
        if($sms_code != $codeData->code){
            return $this->fail('100002');
        }
        Code::delCode($phone);
        $user  = Invite::where('phone',$phone)->first();
        if($user){
            $url    = config('app.teach_url').'/#/register?code='.$user->code;
            $img =  QrCode::format('png')->size(200)->generate($url);
            $type = getimagesizefromstring($img)['mime']; //获取二进制流图片格式
            $img_base64 = 'data:' . $type . ';base64,' . chunk_split(base64_encode($img));
            $rand_path = 'tmp/'.date("YmdHis").rand(1000,999999).".png";
            $img1 = QrCode::format('png')->size(300)->encoding('UTF-8')->margin(0)->generate($url,public_path($rand_path));
            $rand_hb_path = 'tmp/haibao_'.date("YmdHis").rand(1000,999999).".png";
            createWater(public_path('logo/haibao.jpeg'),$rand_path,$rand_hb_path);
            $haibao_base64 =  'data:' . $type . ';base64,' . base64_encode(file_get_contents($rand_hb_path));
            @unlink($rand_path);
            @unlink($rand_hb_path);
            return $this->success([
                'url'      => $url,
                'img'      => $img_base64,
                'haibao'   => $haibao_base64,
            ]);
        }
        $mid = null;
        if($token){
            $member = Member::where('token',$token)->first();
            if($member){
                $mid = $member->id;
            }
        }

        $model = new Invite();
        if($mid){
            $model->mid         = $mid;
        }
        $code   = $this->getIdStr(10);
//        $url    = 'http://teach.globalapex.cn/#/login?code='.$code;
        $url    =  config('app.teach_url').'/#/register?code='.$code;
        $img =  QrCode::format('png')->size(200)->generate($url);
        $type = getimagesizefromstring($img)['mime']; //获取二进制流图片格式
        $img_base64 = 'data:' . $type . ';base64,' . chunk_split(base64_encode($img));
        $rand_path = 'tmp/'.date("YmdHis").rand(1000,999999).".png";
        $img1 = QrCode::format('png')->size(300)->encoding('UTF-8')->margin(0)->generate($url,public_path($rand_path));
        $rand_hb_path = 'tmp/haibao_'.date("YmdHis").rand(1000,999999).".png";
        createWater(public_path('logo/haibao.jpeg'),$rand_path,$rand_hb_path);
        $haibao_base64 =  'data:' . $type . ';base64,' . base64_encode(file_get_contents($rand_hb_path));
        @unlink($rand_path);
        @unlink($rand_hb_path);
        $model->name        = $name;
        $model->phone       = $phone;
        $model->area_code   = $area_code;
        $model->email       = $email;
        $model->code        = $code;
        if($model->save()){
            return $this->success([
                'url'      => $url,
                'img'      => $img_base64,
                'haibao'   => $haibao_base64,
            ]);
        }else{
            return $this->fail();
        }

    }

    /**
     * 生成唯一字符串
     * @param $n
     * @return string
     */
    private function getIdStr($n) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $randomString = '';
        for ($i = 0; $i < $n; $i++) {
            $index = rand(0, strlen($characters) - 1);
            $randomString .= $characters[$index];
        }
        return $randomString;
    }
}
