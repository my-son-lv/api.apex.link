<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class WxTempNoticeService
{

    public function returnTempData($data)
    {
        $tmpData = [
            'touser' => $data['openid'],//会员公众号openid
//            'template_id' => 'cbCvR-j_EQW3gFSYnycdSIWpJtIPmtfro9_XjqrvfXM',//模板id
            'url' => isset($data['url']) ? $data['url'] : config('app.company_url'),//跳转URL
            'miniprogram' => [
                'appid' => config('wechat.mini_program.default.app_id'),        //小程序 appid
                'pagepath' => isset($data['page']) ? $data['page'] : 'pages/index/main',   //小程序跳转页面
            ],
            'data' => [
                'first' => $data['title'],
                'remark' => $data['memo'],
            ],
        ];
        foreach ($data['key'] as $k => $v) {
            $tmpData['data'][$k] = $v;
        }
        switch ($data['type']) {
            case 1:
                //会员开通成功提醒
                //{{first.DATA}}
                //用户名：{{keyword1.DATA}}
                //开通时间：{{keyword2.DATA}}
                //到期时间：{{keyword3.DATA}}
                //{{remark.DATA}}

                //您的会员账号已经开通，尽情使用吧！
                //用户名：千里草
                //开通时间：2016年02月27日 14：59
                //到期时间：2017年02月27日 14：59
                //有什么问题欢迎您随时咨询。
                $tmpData['template_id'] = '8edH6RQg2nEjwMGl0KIKARcriqPzXawcv1iZm7zWVh4';
                break;
            case 2:
                //预约变更通知
                //{{first.DATA}}
                //订单编号：{{keyword1.DATA}}
                //预约类型：{{keyword2.DATA}}
                //变更内容：{{keyword3.DATA}}
                //{{remark.DATA}}

                //您好，经过沟通，您的服务预约时间已经修改完成！
                //订单编号：002981615384
                //预约类型：上门保洁
                //变更内容：2016年1月25日 下午3点－5点
                //谢谢您的理解，如果有疑问请拨打我们的客服电话。
                $tmpData['template_id'] = 'CpnfOulbSl-UNuXqH9Raj1IzKirE46saeG1lLT3QM-k';
                break;
            case 3:
                //入驻成功通知
                //{{first.DATA}}
                //姓名：{{keyword1.DATA}}
                //入驻时间：{{keyword2.DATA}}
                //{{remark.DATA}}

                //您好，你已入驻成功。
                //姓名：张三
                //入驻时间：2017年5月18日 18:46
                //欢迎您加入我们！
                $tmpData['template_id'] = 'O2qwgs7OeqbhIMpskljGCLzy-mMDDB-xw2NGpinK-7g';
                break;
            case 4:
                //面试情况提醒
                //{{first.DATA}}
                //职位名称：{{keyword1.DATA}}
                //面试开始时间：{{keyword2.DATA}}
                //面试结束时间：{{keyword3.DATA}}
                //{{remark.DATA}}

                //您好。您有新的面试预约。
                //职位名称：Java开发工程师
                //面试开始时间：2015年7月7日 13：00
                //面试结束时间：2015年7月7日 18：00
                //请您尽快处理。
                $tmpData['template_id'] = 'aMRnOkWKaQFyzZPNVqzcKEOjxIeh15PkBU9mm91vqZI';
                break;
            case 5://已弃用
                //注册成功通知
                //{{first.DATA}}
                //会员帐号：{{keyword1.DATA}}
                //注册时间：{{keyword2.DATA}}
                //{{remark.DATA}}

                //您好，您已成功注册
                //会员帐号：出门没带钱
                //注册时间：2017年8月22日18:36分
                //感谢您的注册，有疑问请联系客服！
                $tmpData['template_id'] = 'cbCvR-j_EQW3gFSYnycdSIWpJtIPmtfro9_XjqrvfXM';
                break;
            case 6:
                //拒绝面试邀请通知
                //{{first.DATA}}
                //公司名称：{{keyword1.DATA}}
                //职位名称：{{keyword2.DATA}}
                //投递时间：{{keyword3.DATA}}
                //{{remark.DATA}}

                //小明已经拒绝了您的面试邀请
                //公司名称：光华科技
                //职位名称：JAVA
                //投递时间：2016-08-09
                //不要气馁
                $tmpData['template_id'] = 'fD0NYZAlUHLHwsA9h3nVUrUMs_ySBgu3jNF0Vpq0AIo';
                break;
            case 7:
                //预约面试通知
                //{{first.DATA}}
                //面试时间：{{keyword1.DATA}}
                //人才姓名：{{keyword2.DATA}}
                //联系方式：{{keyword3.DATA}}
                //面试岗位：{{keyword4.DATA}}
                //{{remark.DATA}}

                //有新的面试预约，注意做好接待工作~
                //面试时间：2016年8月15日 14：00
                //人才姓名：王小波
                //联系方式：15601880200
                //面试岗位：前厅服务生
                //点击查看详情，了解更多信息
                $tmpData['template_id'] = 'maCDvKoiqlaEBWO6yTy9-_BvDjdMBI2qupUxxb8Y19o';
                break;
            case 8:
                //入驻申请失败通知
                //{{first.DATA}}
                //申请结果：{{keyword1.DATA}}
                //失败原因：{{keyword2.DATA}}
                //审核时间：{{keyword3.DATA}}
                //{{remark.DATA}}

                //您的入驻申请审核未通过!
                //申请结果：审核失败
                //失败原因：申请信息不全
                //审核时间：2020-02-18 10:20:30
                //感谢您的支持，如有疑问联系客服
                $tmpData['template_id'] = 'wPZMIsr8vbQEZQrbUmVhZO0z2gGmJTvA9dFusDXGkGw';
                break;
            case 9:
                //新简历通知
                //{{first.DATA}}
                //新简历：{{keyword1.DATA}}
                //未读简历：{{keyword2.DATA}}
                //{{remark.DATA}}

                //尊敬的用户，您收到了新的简历，请您留意查阅
                //新简历：5份
                //未读简历：2份
                //您可以点击详情，立即查看简历！
                $tmpData['template_id'] = 'pwpPIcNNviqnfVWBY50HchvsbuvOZMWx2FHIKE5Cf5k';
                break;
            case 10:
                //{{first.DATA}}
                //推荐职位：{{keyword1.DATA}}
                //被推荐人：{{keyword2.DATA}}
                //工作年限：{{keyword3.DATA}}
                //学历：{{keyword4.DATA}}
                //毕业院校：{{keyword5.DATA}}
                //{{remark.DATA}}

                //猎头姓名（联系方式）为您推荐新人选，请及时处理。
                //推荐职位：高级产品经理
                //被推荐人：夏天 （ID：29382873）
                //工作年限：3年
                //学历：本科
                //毕业院校：学校名称
                $tmpData['template_id'] = '9rWUdjzVIIuU98Fu5QpKlyzwthQeLWavJvdPsPJA2-A';
                break;
            case 11:
                //{{first.DATA}}
                //操作流程：{{keyword1.DATA}}
                //客服电话：{{keyword2.DATA}}
                //{{remark.DATA}}

                //欢迎关注神工007用户版，您可以通过公众号进行服务预约。
                //操作流程：通过公众号进行服务预约
                //客服电话：400-007-1515
                //如有疑问，请联系神工007客服电话：400-007-1515。
                $tmpData['template_id'] = '4hMTJpdyi8sn_FUASYBIw-C5gHJsAnlEx7G3pjxvjYA';
                break;
        }
        return $tmpData;
    }
}