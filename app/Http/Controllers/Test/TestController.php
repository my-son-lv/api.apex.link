<?php

namespace App\Http\Controllers\Test;

use App\Jobs\SendEmail;
use App\Models\Companys;
use App\Models\Country;
use App\Models\Files;
use App\Models\Job;
use App\Models\JobMate;
use App\Models\Member;
use App\Models\MemberInfo;
use App\Models\MemberInfoChecked;
use App\Services\EmailService;
use App\Services\TeachersService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Mail\Mailer;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Mrgoon\AliSms\AliSms;
use OSS\OssClient;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use TheSeer\Tokenizer\Exception;
use Tencent;
use Image;

class TestController extends Controller
{
    public function __construct()
    {

    }

    public function test(Request $request){

        //
        /*$type = 2;
        $data['mid'] = 51;
        $data['jid'] = 77;
        $this->dispatch(new \App\Jobs\JobMate());*/













//        $email = ['lily@apex.link','shirley@apex.link','1010377549@qq.com','595702382@qq.com','liangjucai@163.com','maomaoxian123@gmail.com'];
//        $email = ['liangjucai@163.com'];
////        $email = Member::all();
////
//        foreach ($email as $k => $v){
//            dispatch(new SendEmail(['email' => $v,'title' => 'Merry Christmas – Greetings from APEX GLOBAL']));
//        }
        echo "完成";

//        var_dump($this->testService->find(1));
//        $email = '763114070@qq.com';
//        Mail::send('email.test',['name' => '梁巨才'],function($message)use($email){
//            $message->to($email)->subject('1111111111111');
//            $message->attach('E:\WWW\PHP\public/test.pdf', ['as' => 'aaa.pdf']);
//        });
    }

    /*public function testSendSms(){
        var_dump($this->aliyunSendSms(13521339391,'SMS_205315358',123456));
    }*/



    public function getTest(Request $request){
        //获取参数
        $url = $request->get('url');
        //公众号的appid、secret
        $appid = "wxecbaab0f6ae16c74";
        $secret = "259776c9ddfcb29bea690279e5228f9c";
        //获取access_token的请求地址
        $accessTokenUrl = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=$appid&secret=$secret";
        //请求地址获取access_token
        $accessTokenJson = file_get_contents($accessTokenUrl);
        $accessTokenObj = json_decode($accessTokenJson);
        $accessToken = $accessTokenObj->access_token;

        //获取jsapi_ticket的请求地址
        $ticketUrl = "https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token=$accessToken&type=jsapi";
        $jsapiTicketJson = file_get_contents($ticketUrl);
        $jsapiTicketObj = json_decode($jsapiTicketJson);
        $jsapiTicket = $jsapiTicketObj->ticket;
        //随机生成16位字符
        $noncestr = str_random(16);
        //时间戳
        $time = time();
        //拼接string1
        $jsapiTicketNew = "jsapi_ticket=$jsapiTicket&noncestr=$noncestr&timestamp=$time&url=$url";
        //对string1作sha1加密
        $signature = sha1($jsapiTicketNew);
        //存入数据
        $data = [
            'appid' => $appid,
            'timestamp' => $time,
            'nonceStr' => $noncestr,
            'signature' => $signature,
            'jsapiTicket' => $jsapiTicket,
            'url' => $url,
            'jsApiList' => [
                'api' => '#'
            ]
        ];
        //返回
        return json_encode($data);
    }

    public function xcxClose(Request $request){
        if($request->show == 'true'){
            Cache::forever('xcx_show',1);
        }else{
            Cache::forever('xcx_show',2);
        }
        echo "当前SHOW:".Cache::get('xcx_show');
    }

    public function Year(Request $request){
        Cache::forever('year_xlh',$request->xlh ?? '');
        echo $request->xlh ? '序列号已设置为：'.$request->xlh : '序列号功能已关闭';
    }

    public function baidu(Request $request){
        $down = $request->down ?? 0;
        $num = 0;
        $page_size = 20;//isset($_GET['page_size'] ) ? ($_GET['page_size'] <= 100 ?? 100) : 20;
        $page = $request->page ?? 1;
        $page = $page - 1;
        $key = $request->key ?? '国际幼儿园';
        $region = $request->region ?? '哈尔滨';
        $url = 'http://api.map.baidu.com/place/v2/search?query=' . $key . '&region=' . $region . '&output=json&ak=O1Z4BmYqOWZXWDvhnCVD6XRqEjXQ01FU&page_num=' . $page . '&page_size=' . $page_size;
        $data['total'] = 1;
        $res = file_get_contents($url);
        $data = json_decode($res, true);
        $excel_data = [];
        if (isset($data['results'])) {
            foreach ($data['results'] as $k => $v) {
                $num++;
                if(!$down) {
                    echo "<br>" . "第" . $num . "条数据" . "<br>";
                    echo @$v['name'] . "<br>";
                    echo @$v['address'] . "<br>";
                    echo @$v['telephone'] . "<br>";
                }else{
                    $excel_data[] = [@$v['name'] ?? '无', @$v['address'] ?? '无', @$v['telephone'] ? ' '.$v['telephone'] : '无'];
                }
            }
            if($down) {
                putCsv($key.'_'.$region.'_第'. ($page+1) .'页',$excel_data,['机构名称','机构地址','机构电话']);
            }
        }else{
            echo "<br> 查询失败了，原因".$data['message'];
        }
    }

    public function gaode(Request $request){
        $down = $request->down ?? 0;
        $num = 0;
        $page_size = 20;//isset($_GET['page_size'] ) ? ($_GET['page_size'] <= 100 ?? 100) : 20;
        $page = $request->page ?? 1;
        $key = $request->key ?? '国际幼儿园';
        $region = $request->region ?? '哈尔滨';
        $data['total'] = 1;
        $pageStart = ($page-1) * 3 + 1;
        $pageEnd  = $page * 3;
        for($i = $pageStart ; $i <= $pageEnd ; $i++){
            $url = 'http://restapi.amap.com/v3/place/text?keywords='.$key.'&city='.$region.'&offset='.$page_size.'&page='.$i.'&key=21fbc3ef2764a40f6bfe9e98c2d9c25b';
            $res = file_get_contents($url);
            $data = json_decode($res, true);
            $excel_data = [];
            if (isset($data['pois']) && count($data['pois'])) {
                foreach ($data['pois'] as $k => $v) {
                    $num++;
                    if(!$down) {
                        echo "<br>" . "第" . $num . "条数据" . "<br>";
                        echo @$v['name'] . "<br>";
                        echo (@count($v['address']) ? @$v['address'] : '') . "<br>";
                        echo (@count($v['tel']) ? @$v['tel'] : '') . "<br>";
                    }else{
                        $excel_data[] = [@$v['name'] ?? '无', @count($v['address']) ? @$v['address'] : '无' , @count($v['tel']) ? @$v['tel'] : '无'];
                    }
                }
                if($down) {
//                    putCsv($key.'_'.$region.'_第'. ($page) .'页',$excel_data,['机构名称','机构地址','机构电话']);
                }
            }else{
                echo "<br> 查询失败了，没有更多了";
            }
        }



    }


    public function test1111111111(){
        /*$token = $this->eSignAutoLogin();
        $accoountId = $this->eSignAutoCrateUser(date('YmdHis').rand(100000,999999),'梁巨才',$token);
        $org_id = $this->eSignAutoCrateOrganize(date('YmdHis').rand(100000,999999),'凌晨四点(北京)科技有限公司',$accoountId,$token);
        $url = $this->eSignAutoGetUrl($org_id,$accoountId,$token);
        var_dump($data);

        exit;*/


//        exit;
        /*$user_arr = [
            'name'  => '梁巨才',
            'idNo'  => '130706199304190912',
        ];*/
        $header = array(
            "Content-Type: application/json",
            "Accept: application/json"
        );
        /*$res = postJsonCurl(config('esign.E_SIGN_HOST_URL').config('esign.E_SIGN_ADD_PERSON_URL'),$user_arr,$header);
        $res = json_decode($res,true);
        echo "个人:".$res['accountId'].PHP_EOL;*/
        /*$organize_arr = ['name' => '北京凌晨四点','organCode'=> '91110105MA01QHH711','regType'=>'MERGE'];
        $res = postJsonCurl(config('esign.E_SIGN_HOST_URL').config('esign.E_SIGN_ADD_ORGANIZE_URL'),$organize_arr,$header);
        $res = json_decode($res,true);
        echo "企业:".$res['accountId'].PHP_EOL;
        //企业创建印章
        $organize_arr = ['accountId' => '575DBC3D340645ABAEE7860EE5C2E948','color'=> 'RED','templateType'=>'STAR'];
        $res1 = postJsonCurl(config('esign.E_SIGN_HOST_URL').config('esign.E_SIGN_ADD_ORGANIZE_SEAL'),$organize_arr,$header);
        echo "创建企业印章:".$res1.PHP_EOL;*/

        //平台自签署
        $sign = [
            'signPos'   => [
                "posPage" =>"1",
                 "posX"=>225,
                 "posY"=>150,
                "posPage"=>"1-2",
            ],
            'signType'  => 'Multi',
            'sealId'    => '',
            'file'      => [
                'srcPdfFile'    => '/usr/local/www/api.globalapex.cn/public/test_1.pdf',
                'dstPdfFile'    => '/usr/local/www/api.globalapex.cn/public/test1.pdf'
            ],
        ];
        $res = postJsonCurl(config('esign.E_SIGN_HOST_URL').config('esign.E_SIGN_SELF_FILE_SIGN'),$sign,$header);
        var_dump($res);
        //平台自签署
        $sign = [
            'signPos'   => [
                "posPage" =>"1",
                "posX"=>185,
                "posY"=>650,

            ],
            'signType'  => 'Multi',
            'sealId'    => '',
            'file'      => [
                'srcPdfFile'    => '/usr/local/www/api.globalapex.cn/public/test1.pdf',
                'dstPdfFile'    => '/usr/local/www/api.globalapex.cn/public/test2.pdf'
            ],
        ];
        $res = postJsonCurl(config('esign.E_SIGN_HOST_URL').config('esign.E_SIGN_SELF_FILE_SIGN'),$sign,$header);
        var_dump($res);
        /*echo "平台自签署结果：".$res;
        //平台用户签署
        $sign = [
            'signPos'   => [
                "posType" =>"0",
                "posX"=>425,
                "posY"=>725,
                "posPage"=>"1-2",
            ],
            'accountId' =>  '575DBC3D340645ABAEE7860EE5C2E948',
            'signType'  => 'Multi',
            'sealData' => json_decode($res1,true)['sealData'],
            'file'      => [
                'srcPdfFile'    => '/usr/local/www/api.globalapex.cn/public/test1.pdf',
                'dstPdfFile'    => '/usr/local/www/api.globalapex.cn/public/test2.pdf'
            ],
        ];
        $res = postJsonCurl(config('esign.E_SIGN_HOST_URL').config('esign.E_SIGN_USER_FILE_SIGN'),$sign,$header);
        echo '平台用户签署结果'.$res;*/
    }
}
