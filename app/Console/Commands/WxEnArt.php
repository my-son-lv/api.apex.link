<?php

namespace App\Console\Commands;

use App\Models\WxArticleEn;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WxEnArt extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:WxEnArt';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
        DB::beginTransaction();
        try {
            $get_token_url  = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='.config('wxgzh.WX_GZH_APP_ID_EN').'&secret='.config('wxgzh.WX_GZH_SECRET_EN');
            $res = curl_get($get_token_url);
            $token  = json_decode($res,true)['access_token'];
            $news_count_url = 'https://api.weixin.qq.com/cgi-bin/material/get_materialcount?access_token='.$token;
            //获取图文总数
            $news_count =  json_decode(file_get_contents($news_count_url),true)['news_count'];
            //获取最大数
            $max = WxArticleEn::max('page') ?? 0;
            //循环 从总数->最大数 读入数据库
            for($i = $news_count ; $i > $max ; $i--){
                $get_token_url  = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='.config('wxgzh.WX_GZH_APP_ID_EN').'&secret='.config('wxgzh.WX_GZH_SECRET_EN');
                $res = curl_get($get_token_url);
                $token  = json_decode($res,true)['access_token'];
                $get_list_url   = 'https://api.weixin.qq.com/cgi-bin/material/batchget_material?access_token='.$token;
                $reslist = postJsonCurl($get_list_url,['type' => 'news','offset' => $i - $max - 1 , 'count' => 1]);
                $reslist = json_decode($reslist,true);
                foreach ($reslist['item'][0]["content"]["news_item"] as $k => $v){
                    $model = new WxArticleEn();
                    $model->title       = $v['title'];
                    $model->desc       = $v['digest'];
                    $model->thumb_url   = $v['thumb_url'];
                    $model->url         = $v['url'];
                    $model->time        = date("Y-m-d H:i:s",$reslist['item'][0]['update_time']);
                    $model->page        = ($news_count - $i) + 1;
                    if(!$model->save()){
                        DB::rollBack();
                        echo "插入失败".json_encode($model);
                    }
                }
            }
            DB::commit();
            echo "插入完成";
        }catch (\Exception $e){
            DB::rollBack();
            echo "出错：".$e->getMessage();
        }
        /*$get_token_url  = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='.config('wxgzh.WX_GZH_APP_ID_EN').'&secret='.config('wxgzh.WX_GZH_SECRET_EN');
        $res = curl_get($get_token_url);
        $token  = json_decode($res,true)['access_token'];
        $get_list_url   = 'https://api.weixin.qq.com/cgi-bin/material/batchget_material?access_token='.$token;
        $reslist = postJsonCurl($get_list_url,['type' => 'news','offset' => 1 , 'count' => 1]);
        var_dump($reslist);exit;
        $reslist = json_decode($reslist,true);
        var_dump($reslist);
        $list = [];
        foreach ($reslist['item'] as $k => $v){
            $itme = [
                'title' => $v['content']['news_item'][0]['title'],
                'desc' => $v['content']['news_item'][0]['digest'],
                'thumb_url' => $v['content']['news_item'][0]['thumb_url'],
                'url' => $v['content']['news_item'][0]['url'],
                'create_time' => date('Y.m.d H:i:s',$v['content']['create_time']),
            ];
            $list['data'][] = $itme;
        }*/
    }
}
