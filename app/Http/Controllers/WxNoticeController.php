<?php

namespace App\Http\Controllers;

use App\Jobs\SendWxNotice;
use App\Models\Job;
use App\Models\Official;
use App\Services\WxTempNoticeService;
use EasyWeChat\Kernel\Messages\Image;
use EasyWeChat\Kernel\Messages\Text;
use Illuminate\Auth\Events\Login;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WxNoticeController extends Controller
{
    protected $official;
    protected  $wxTempNoticeService;

    public function __construct(Official $official,WxTempNoticeService $wxTempNoticeService)
    {
        $this->official = $official;
        $this->wxTempNoticeService = $wxTempNoticeService;
    }

    /**
     * 用户关注/取消关注 插入数据库
     * @param Request $request
     * @return mixed
     */
    public function wxGzhMessageNotice(Request $request){
        /*$signature = $request->signature;
        $timestamp = $request->timestamp;
        $nonce     = $request->nonce;
        $echostr   = $request->echostr;
        //2vXuNpcqca0RZtYmHbEIR0YsSX2O0nyuEj1lqppdDOd
        $token = config('wxgzh.WX_MSG_TOKEN');
        $tmpArr = array($token, $timestamp, $nonce);
        sort($tmpArr, SORT_STRING);
        $tmpStr = implode( $tmpArr );
        $tmpStr = sha1( $tmpStr );
        if ($tmpStr == $signature ) {
            Log::info('微信消息通知，请求方式：'.$request->method());
            Log::info(json_encode($request->all()));
            return $echostr;
        }*/

        $app = \EasyWeChat::officialAccount();
        $user = $app->user->get($request->openid);
        Log::info('公众号用户信息：'.json_encode($user));
        $official = $this->official;
        $app->server->push(function ($message) use ($user,$official,$request) {
            Log::info('公众号通知:'.json_encode($message));
            switch ($message['MsgType']) {
                case 'event':
                    //关注subscribe
                    $info = $official->where('openid',$request->openid)->first();
                    if($message['Event'] == 'subscribe'){
                        $user['status'] = 1;
                        $user['time'] = date("Y-m-d H:i:s");
                        if($info){
                            $info->update($user);
                        }else{
                            $official->create($user);
                        }
                        //发送微信通知
                        $wxNoticeData = [
                            'openid' => $request->openid,
                            'type' => 11,
                            'title' => '欢迎关注寰球阿帕斯外籍人才招聘平台，您可通过小程序查看外教详情。',
                            'memo' => '如需咨询，请电话或者微信联系客服17001213999。',
                            'key' => [
                                'keyword1' => '小程序搜索“寰球阿帕斯”',
                                'keyword2' => '17001213999',
                            ],
                        ];
                        $this->dispatch(new SendWxNotice($wxNoticeData));
                    }else if($message['Event'] == 'unsubscribe'){
                        //取消unsubscribe
                        if($info){
                            $info->update(['status' => 2,'time' => date("Y-m-d H:i:s")]);
                        }
                    }
//                    return '收到事件消息';
                    break;
//                case 'text':
//                    return '收到文字消息';
//                    break;
//                case 'image':
//                    return '收到图片消息';
//                    break;
//                case 'voice':
//                    return '收到语音消息';
//                    break;
//                case 'video':
//                    return '收到视频消息';
//                    break;
//                case 'location':
//                    return '收到坐标消息';
//                    break;
//                case 'link':
//                    return '收到链接消息';
//                    break;
//                case 'file':
//                    return '收到文件消息';
//                // ... 其它消息
//                default:
//                    return '收到其它消息';
//                    break;
            }
        });
//        $accessToken = $app->access_token;
//        Log::info('111:'.json_encode($accessToken));
//        $token = $accessToken->getToken(true);
//        Log::info('token:'.json_encode($token));
        return $app->server->serve();
    }

    //微信客服自动回复二维码
    public function wxMessageNotice(Request $request){
        /*$signature = $request->signature;
        $timestamp = $request->timestamp;
        $nonce     = $request->nonce;
        $echostr   = $request->echostr;
        //2vXuNpcqca0RZtYmHbEIR0YsSX2O0nyuEj1lqppdDOd
        $token = config('wxgzh.WX_MSG_TOKEN');
        $tmpArr = array($token, $timestamp, $nonce);
        sort($tmpArr, SORT_STRING);
        $tmpStr = implode( $tmpArr );
        $tmpStr = sha1( $tmpStr );
        if ($tmpStr == $signature ) {
            Log::info('微信消息通知，请求方式：'.$request->method());
            Log::info(json_encode($request->all()));
            return $echostr;
        }*/

        $app = \EasyWeChat::miniProgram();
        $result = $app->media->uploadImage('wx_gzh/gzh_ewm.jpg');
        Log::info('临时素材上传结果：'.json_encode($result));
        if(!isset($result['media_id'])){
            exit();
        }
        $app->server->push(function ($message) use ($app,$result) {
            switch ($message['MsgType']) {
                case 'event':
//                    return '收到事件消息';
//                    $app->customer_service->message(new Image($result['media_id']))->to($message['FromUserName'])->send();
                    break;
                case 'text':
                    //回复一条文本消息
//                    $app->customer_service->message(new Text('sdfad'))->to($message['FromUserName'])->send();
                    $app->customer_service->message(new Image($result['media_id']))->to($message['FromUserName'])->send();
                        break;
//                            //回复一条链接消息
//                            $data = [];
//                            $data['touser']= $message['FromUserName'] ;
//                            $data['msgtype'] = 'link';
//                            $data['link']= [
//                                'title'=>$result['title'],
//                                'description'=>$result['description'],
//                                'url'=>$result['url'],
//                                'thumb_url'=>$fileModel->getPath($result['image_id'])
//                            ];
//                            $content = new Raw(json_encode($data));
//                            $app->customer_service->message($content)->to($message['FromUserName'])->send();
//                            break;
                    // return '收到文字消息';
                    break;
                case 'miniprogrampage':
                    // return '收到卡包消息';
                    break;
                case 'image':
                    // return '收到图片消息';
                    break;
                case 'voice':
                    // return '收到语音消息';
                    break;
                case 'video':
                    // return '收到视频消息';
                    break;
                case 'location':
                    // return '收到坐标消息';
                    break;
                case 'link':
                    // return '收到链接消息';
                    break;
                case 'file':
                    // return '收到文件消息';
                    // ... 其它消息
                default:
                    // return '收到其它消息';
                    break;
            }
        });
        $response = $app->server->serve();
        $response->send();


    }




    public function sendTempNotice(Request $request){
        /*$data = [
            'openid' => 'oqbHh5iAoUDFzhqiaZwcDl9XNHMM',
            'type' => 1,
            'title' => '会员开通标题测试',
            'memo'  => '您的会员已开通，有问题随时联系我们。',
            'key' => ['keyword1' => '梁巨才','keyword2' => date("Y-m-d"),'keyword3' => date("Y-m-d",strtotime("+10 month")) ],
        ];
        $this->dispatch(new SendWxNotice($data));*/
        /*$app = \EasyWeChat::officialAccount();

        $res = $app->template_message->send($this->wxTempNoticeService->returnTempData($data));
        Log::info(json_encode($res));
        if($res['errcode']){
            return $this->fail(1000000,$res['errmsg']);
        }*/
        /*$res = $app->template_message->send([
            'touser' => 'oqbHh5iAoUDFzhqiaZwcDl9XNHMM',
            'template_id' => 'cbCvR-j_EQW3gFSYnycdSIWpJtIPmtfro9_XjqrvfXM',
            'url' => 'https://easywechat.org',
            'miniprogram' => [
                'appid' => config('wechat.mini_program.default.app_id'),
                'pagepath' => 'pages/index/main',
            ],
            'data' => [
                'first' => 'test_title',
                'keyword1' => 'lingjucai',
                'keyword2' => date("Y-m-d H:i:s"),
                'remark' => '感谢您使用上岛咖啡静安寺考虑到房价撒酒疯卢卡斯架飞机撒飞机撒两地分居到拉萨解放路sad街坊邻居萨芬姜辣素',
            ],
        ]);
        Log::info(json_encode($res));
        if($res['errcode']){
            return $this->fail(1000000,$res['errmsg']);
        }*/
    }

}
