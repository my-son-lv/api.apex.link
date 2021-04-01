<?php

namespace App\Http\Controllers\Website;

use App\Models\Website;
use App\Models\WxArticleEn;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class WebsiteController extends Controller
{
    public function getJob(Request $request){
        $page       = $request->get('page',1);
        $pageSize   = $request->get('pageSize',10);
        $id         = $request->get('id',0);
        $data = config('job');
        $start=($page-1)*$pageSize;
        $list = array_slice($data,$start,$pageSize);
        return $this->success(['list' => $list,'page' => $page , 'pageSize' => $pageSize , 'total' => count($data),'count' => ceil(count($data)/$pageSize)]);
    }

    //
    public function message(Request $request,Website $website){
        $name                   = $request->get('name','');
        $contact_person         = $request->get('contact_person','');
        $contact_information    = $request->get('contact_information','');
        $ip                     = $request->getClientIp();
        if(!$name || !$contact_person || !$contact_information){
            return $this->fail(100001);
        }
        $flg = $website->where('ip',$ip)->where('created_at','>',date('Y-m-d H:i:s',strtotime('-30second')))->count();
        if($flg){
            return $this->fail(100007);
        }
        $data = $request->only('name','contact_person','contact_information');
        $data['ip'] = $ip;
        $msg = $website->create($data);
        return $this->success($msg);
    }

    public function getEnArticleList(Request $request){
        $page       = $request->get('page',1);
        $pageSize   = $request->get('pageSize',10);

        $data = WxArticleEn::orderBy('time','desc');
        $count = $data->count();
        $list['data'] = $data->offset(($page-1)*$pageSize)->limit($pageSize)->get();
        $list['total'] = $count;
        $list['pageSize'] = $pageSize;
        $list['page'] = $page;
        return $this->success($list);
    }

    public function getArticleList(Request $request){
        $page       = $request->get('page',1);
        $pageSize   = $request->get('pageSize',10);
        $limit      = ($page-1) * $pageSize;

        $get_token_url  = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='.config('wxgzh.WX_GZH_APP_ID').'&secret='.config('wxgzh.WX_GZH_SECRET');
        $res = curl_get($get_token_url);
        $token  = json_decode($res,true)['access_token'];
        $get_list_url   = 'https://api.weixin.qq.com/cgi-bin/material/batchget_material?access_token='.$token;
        $reslist = postJsonCurl($get_list_url,['type' => 'news','offset' => $limit , 'count' => $pageSize]);
        $reslist = json_decode($reslist,true);
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
        }
        $list['total'] = $reslist['total_count'];
        $list['pageSize'] = $pageSize;
        $list['page'] = $page;
        return $this->success($list);
    }
}
