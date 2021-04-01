<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
</head>

<body>
<div class="content">
    <div class="t1">外籍人才证件办理服务协议</div>
    <div class="t2 mt_40">甲方（委托方）：{{$model['b_company_name']}}</div>
    <div class="t2 mt_20">乙方（受托方）：凌晨四点（北京）科技有限公司</div>
    <div class="t3 mt_20">甲方委托乙方办理外籍人才工作许可及管理服务。双方本着自愿、平等、诚信、互利的原则，根据相关法律法规的规定，签订本合同，目的在于确定甲、乙双方的权利和义务，共同遵守如下条款：
    </div>
    <div class="t2 mt_40">第一条服务内容及费用标准</div>
    <div class="t3 mt_20">
        <table border="1">
            <tr>
                <th>服务项目</th>
                <th>收费标准(人民币）</th>
                <th>是否委托</th>
                <th>委托人次</th>
            </tr>
            <tr>
                <td>办理聘外许可开户</td>
                <td>1800/次</td>
                <td>{{@$contract_json['bus1']}}</td>
                <td>{{explode(',',$contract_json['num'])[0]}}</td>
            </tr>
            <tr>
                <td>办理国内转聘工作许可证</td>
                <td>3800/人次</td>
                <td>{{@$contract_json['bus2']}}</td>
                <td>{{explode(',',$contract_json['num'])[1]}}</td>
            </tr>
            <tr>
                <td>新办工作许可证（国内）</td>
                <td>4800/人次</td>
                <td>{{@$contract_json['bus3']}}</td>
                <td>{{explode(',',$contract_json['num'])[2]}}</td>
            </tr>
            <tr>
                <td>新办工作许可证（国外）</td>
                <td>5800/人次</td>
                <td>{{@$contract_json['bus4']}}</td>
                <td>{{explode(',',$contract_json['num'])[3]}}</td>
            </tr>
            <tr>
                <td>合计</td>
                <td></td>
                <td></td>
                <td>{{$contract_json['money1']}}</td>

            </tr>
        </table>
    </div>

    <div class="t3 mt_20">注：以上为含税价格，不包括办理上述事宜的材料翻译、材料认证的费用。</div>

    <div class="t2 mt_40">第二条定义</div>
    <div class="t3 mt_20">1.办理聘外许可开户是指根据外国人来华工作管理条例规定，协助甲方开通聘外许可账号并具备合法招聘外籍人才的资质。</div>
    <div class="t3 mt_20">2.办理国内转聘工作许可证是指协助外籍人才申请并获得新的工作许可证。</div>
    <div class="t3 mt_20">3.新办工作许可证（国内）是指协助在中国境内的外籍人才申请并获得新的工作许可证。</div>
    <div class="t3 mt_20">4.新办工作许可证（国外）是指协助在境外的外籍人才申请并获得新的工作许可证。</div>

    <div class="t2 mt_40">第三条甲方权利义务</div>
    <div class="t3 mt_20">1.本协议签署后，甲方应指定工作人员，负责与乙方对接具体工作。</div>
    <div class="t3 mt_20">2.甲方应按照约定时间及时向乙方提供申请办理本协议约定的服务内容所需要的材料，并确保材料准确、真实、合法有效。因甲方提供材料不及时或不符合规定，造成委托事项延迟办理或无法办理，由甲方承担相应后果，甲方仍应按照本协议支付费用。</div>
    <div class="t3 mt_20">3.甲方有权了解委托事项办理的进展情况。</div>
    <div class="t3 mt_20">4.甲方应按照本协议的约定向乙方支付服务费。</div>

    <div class="t2 mt_40">第四条乙方权利义务</div>
    <div class="t3 mt_20">1.本协议签署后，甲方应指定工作人员，负责与乙方对接具体工作。</div>
    <div class="t3 mt_20">2.乙方应为甲方提供办理上述事宜的咨询服务。</div>
    <div class="t3 mt_20">3.乙方应按照法律规定，审慎、专业、高效地为甲方办理受托事宜，若因乙方过失造成工作许可未能如期签发的，乙方所收取服务费应全款退还。</div>
    <div class="t3 mt_20">4.乙方有权要求甲方按照本协议约定支付服务费。</div>

    <div class="t2 mt_40">第五条服务费及支付方式</div>
    <div class="t3 mt_20">1.服务费：服务费总额为{{$contract_json['money2']}}元。</div>
    <div class="t3 mt_20">2.支付方式：甲方须在合同签订当日向乙方支付合同服务费总额的{{$contract_json['per']}}%，在委托事项办结当日支付剩余款项。乙方指定收款账户信息如下：</div>
    <div class="t3 mt_20">开户名：凌晨四点（北京）科技有限公司</div>
    <div class="t3 mt_20">开户行：中国工商银行长安中海凯旋支行</div>
    <div class="t3 mt_20">银行账号：623271 0200001021120</div>
    <div class="t3 mt_20">3.如乙方办理受托事宜过程中，需要对申请材料进行翻译或认证，甲方应及时向第三方支付相应费用。</div>
    <div class="t3 mt_20">4.甲方未按合同约定向乙方支付服务费的，乙方的服务顺延，因此造成证件未能如期签发的，甲方自行承担。</div>

    <div class="t2 mt_40">第六条不可控因素</div>
    <div class="t3 mt_20">1.双方知晓上述事项办理需向国内外相关主管部门申请，如遇到办理政策或流程改动等，会导致乙方无法准时履行办理服务。双方同意出现不可控因素时，双方均不承担违约责任，但乙方应尽快通知甲方，并为甲方提供相应的事后处理方案。</div>
    <div class="t3 mt_20">2.如因上述不可控因素导致委托事项无法办理，或不再具有办理的实际需要需终止服务的，双方按照以下情况结算服务费：</div>
    <div class="t3 mt_20">a)乙方尚未启动材料准备工作，甲方不再支付服务费，乙方应向甲方返还已收取的费用。</div>
    <div class="t3 mt_20">b)乙方已准备好相关材料但尚未向相关部门提交申请的，甲方应按照本协议服务金额的50%支付。</div>
    <div class="t3 mt_20">c)乙方已向相关部门提交申请的，甲方应按照本协议服务金额的80%支付。</div>

    <div class="t2 mt_40">第七条争议解决</div>
    <div class="t3 mt_20">1.在双方就本合同项中条款的解释和履行发生争议时，双方应以协商解决该争议。如协商不成，任何一方均可向北京市朝阳区人民法院提起诉讼。</div>
    <div class="t3 mt_20">2.本合同的订立、效力、执行和解释及争议的解决均以中华人民共和国大陆地区法律为准据法并排除冲突规范的适用。</div>

    <div class="t2 mt_40">第八条协议效力及其他</div>
    <div class="t3 mt_20">1.本协议双方盖章之日生效。</div>
    <div class="t3 mt_20">2.本合同中标题仅供参考之用，不影响本合同任何部分的涵义及解释。</div>
    <div class="t3 mt_20">3.本合同未尽事宜，经双方书面确认可对本合同做出修改或签订补充合同，双方签署的修改和补充合同是本合同的组成部分，具有与本合同同等的法律效力。</div>
    <div class="t3 mt_20">4.本合同一式两份，甲乙双方各执一份，两份具有同等法律效力。</div>
    <div class="t3 mt_20">（以下无正文）</div>
    <br>
    <br>
    <br>
    <br>
    <br>
    <br>
    <br>
    <br>
    <br>
    <br>
    <br>
    <br>
    <br>

    <div class="t3 mt_20">甲 方：{{$model['b_company_name']}}</div>
    <div class="t3 mt_20">盖 章：</div>
    <div class="t3 mt_20">日 期：{{substr($model['start_date'],0,10)}}</div>
    <div class="t3 mt_20">乙 方：凌晨四点（北京）科技有限公司</div>
    <div class="t3 mt_20">盖 章：</div>
    <div class="t3 mt_20">日 期：{{substr($model['start_date'],0,10)}}</div>
</div>

<style type="text/css">
    .content {
        padding: 40px;
        background: white;
        margin-top: 20px;
        width: 794px;
        margin: 0 auto;
    }

    .t1 {
        font-size: 20px;
        font-weight: 500;
        color: #333333;
        text-align: center;
    }

    .t2 {
        font-size: 16px;
        font-family: PingFangSC-Regular, PingFang SC;
        font-weight: 600;
        color: #333333;
    }

    .t3 {
        font-size: 16px;
        font-weight: 400;
        color: #333333;
        line-height: 22px;
        text-indent: 2em;
    }

    .mt_20 {
        margin-top: 20px;
    }

    .mt_40 {
        margin-top: 40px;
    }

    .check {
        display: block;
        font-size: 16px;
        font-family: PingFangSC-Regular, PingFang SC;
        font-weight: 400;
        color: #333333;
        line-height: 22px;
        margin-top: 5px;
        margin-left: 2em;
    }


    .rt_btn_1 {
        font-size: 14px;
        font-weight: 400;
        color: #FFFFFF;
        width: 144px;
        height: 32px;
        background: #078CA9;
        padding: 0;
        position: absolute;
        right: 140px;
        top: 72px;
    }

    .rt_btn_2 {
        width: 70px;
        height: 22px;
        font-size: 14px;
        font-weight: 400;
        color: #333333;
        width: 102px;
        height: 32px;
        background: #FAFAFA;
        border: 1px solid #DDDDDD;
        padding: 0;
        position: absolute;
        right: 20px;
        top: 72px;
    }

    .l_btn {
        width: 88px;
        height: 32px;
        background: #078CA9;
        font-size: 14px;
        font-weight: 400;
        color: #FFFFFF;
        padding: 0;
    }

    .r_btn {
        width: 74px;
        height: 32px;
        background: #FAFAFA;
        border: 1px solid #DDDDDD;
        font-size: 14px;
        font-weight: 400;
        color: #333333;
        padding: 0;
        margin-left: 20px;
    }

    .right {
        font-size: 14px;
        width: 15px;
        height: 15px;
        line-height: 17px;
        border: 1px solid black;
        text-align: center;
        border-radius: 3px;
        display: inline-block;
    }
</style>
<script type="text/javascript"></script>
</body>

</html>