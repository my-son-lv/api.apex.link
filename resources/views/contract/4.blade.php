<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
</head>

<body>
<div class="content">
    <div class="t1">APEX GLOBAL会员服务合同</div>
    <div class="t2 mt_20">甲方：{{$model['b_company_name']}}</div>
    <div class="t2 mt_20">地址：{{$contract_json['address']}}</div>
    <div class="t2 mt_20">联系人：{{$model['b_name']}}</div>
    <div class="t2 mt_20">电话：{{$model['b_phone']}}</div>
    <div class="t2 mt_20">邮箱：{{$contract_json['email']}}</div>
    <div class="t2 mt_40">乙方：凌晨四点（北京）科技有限公司</div>
    <div class="t2 mt_20">地址：北京市朝阳区望京园601号楼悠乐汇E座807室</div>
    <div class="t2 mt_20">联系人：安奎</div>
    <div class="t2 mt_20">电话：17801160565</div>
    <div class="t3 mt_20">招聘性质：企业自招</div>
    <div class="t3 mt_20">甲、乙双方经友好协商，现就甲方委托乙方在其所属“APEX
        GLOBAL（以下称：寰球阿帕斯）”网络平台上为甲方提供招聘信息服务以及推广服务等事宜达成一致，现根据有关法律、法规的规定，甲乙双方达成如下协议。</div>
    <div class="t2 mt_40">第一条服务内容</div>
    <div class="t3 mt_20">乙方向甲方提供寰球阿帕斯会员套餐服务，服务内容包括：</div>
    <div class="t3 mt_20">
        <table border="1">
            <tr>
                <th>会员类型</th>
                <th>价格</th>
                <th>服务期</th>
                <th>职位发布</th>
                <th>下载简历</th>
                <th style="width: 200px;">增值服务</th>
            </tr>
            <tr>
                <td>季度会员</td>
                <td>3980元</td>
                <td>3个月</td>
                <td>20个</td>
                <td>30份</td>
                <td>
                    <br />
                    1对1招聘顾问<br />
                    职位置顶5个<br />
                    查看简历不限次数<br />
                    线上面试不限次数<br />
                    线上签约不限次数<br />
                    猎头业务享9折<br />
                    签证业务享9折<br />
                    <br />
                </td>
            </tr>
        </table>
    </div>
    <div class="t3 mt_20">
        特别声明：甲方签订本协议时已知悉上述服务内容的详细情况。本合同所有服务内容均应在本合同服务期内执行完毕，否则合同到期后，所有未足额使用或履行完的服务时间和内容均视为执行完毕，不予延续和退费。</div>
    <div class="t2 mt_40">第二条服务期限</div>
    <div class="t3 mt_20">1.本合同服务期限为3个月，自乙方收到甲方付款且乙方向甲方开通了本合同第一条约定的会员服务之日起计算。</div>
    <div class="t3">2.乙方在确认合同款项到账的一个工作日内为甲方开通会员服务，甲方另有要求的，以双方协商确定的时间开通会员服务。</div>
    <div class="t2 mt_40">第三条合同金额及支付方式</div>
    <div class="t3 mt_20">1.本合同金额共计人民币{{$contract_json['money_min']}}元，大写{{$contract_json['money_max']}}整，为含税价格。甲方须在合同签订后{{$contract_json['day']}}个工作日内支付至乙方指定账户</div>
    <div class="t3 mt_20">2.乙方收款账户信息：</div>
    <div class="t3">开户名：凌晨四点（北京）科技有限公司</div>
    <div class="t3">开户行：中国工商银行长安中海凯旋支行</div>
    <div class="t3">银行账号：623271 0200001021120</div>
    <div class="t3">乙方对接人邮箱：lily@apex.link</div>
    <div class="t3 mt_20">3.乙方收到甲方支付款项后五个工作日内向甲方出具等额增值税发票。开票信息如下：</div>
    <div class="t3">公司名称：{{$contract_json['kp_companys_name']}} </div>
    <div class="t3">注册地址: {{$contract_json['kp_address']}} </div>
    <div class="t3">电话：{{$contract_json['kp_phone']}} </div>
    <div class="t3">开户行：{{$contract_json['kp_bank_name']}} </div>
    <div class="t3">账号：{{$contract_json['kp_bank_account']}} </div>
    <div class="t3">纳税人识别号：{{$contract_json['kp_tax_account']}} </div>
    <div class="t2 mt_40">第四条甲方权利义务</div>
    <div class="t3 mt_20">1.甲方保证其是依法成立的组织，应向乙方提供自身有效证件的复印件。</div>
    <div class="t3 mt_20">
        2.甲方应确保所发布信息的真实有效性。如甲方发布虚假职位信息（包括虚假薪资待遇、虚假岗位、虚假招聘机构名称、招聘性质和合同不符等）经求职者投诉或乙方工作人员审查并查证属实时，乙方有权终止合同及相关服务并停止甲方继续使用原有会员账户，并对已经收取的会员费用不予退还，且产生的一切责任由甲方承担。
    </div>
    <div class="t3 mt_20">
        3.甲方承诺委托乙方发布的信息是真实合法的，不得违反法律规定或侵犯第三方权利，否则乙方有权终止合同及相关服务并停止甲方继续使用原有会员账户，并对已经收取的会员费用不予退还，且产生的一切责任由甲方承担。</div>
    <div class="t3 mt_20">
        4.甲方负责向乙方提供链接及发布需要的图文信息文件，在确定发布网络招聘信息前，若乙方认为甲方的信息违反法律规定或不符合乙方合规、外链要求的，乙方有权暂停发布相关的网络招聘信息，并要求甲方重新提供符合要求的资料。
    </div>
    <div class="t3 mt_20">5.甲方承诺本合同中约定的招聘信息刊登及简历下载仅服务于甲方单位人才缺口，
        甲方既非从事人才招聘、人才服务等相关性业务的企业，也不会利用乙方的服务为第三方提供人才服务。如乙方确认甲方为从事人才相关服务的企业或甲方采购乙方服务系用做为第三方做人才服务的，乙方有权立即解除合同且不予退款。
    </div>
    <div class="t3 mt_20">
        6.甲方需妥善保管寰球阿帕斯会员网站账号，因甲方原因导致账号密码被盗用引发的损失及相应责任由甲方承担，甲方可立即联系乙方协助找回账号密码。对于将甲方账号交给或借给非甲方使用的情况，一经乙方确认，乙方有权停止甲方的使用资格，或终止合同，并不予以退款。
    </div>
    <div class="t3 mt_20">7.甲方开展业务应当遵守国家及地方法律、规章、制度、政策等，协议签署后如甲方利用本协议服务从事任何违法、违规活动的，乙方有权直接予以账号禁用并终止服务且不予以退款。</div>
    <div class="t3 mt_20">8.甲方需按合同约定向乙方付款，迟延付款的，乙方有权迟延开启服务。</div>
    <div class="t3 mt_20">9.甲方违反本条条款给乙方带来损失的，甲方应向乙方赔偿损失（包括但不限于直接损失、律师费、解决争议之和解金、赔偿金、诉讼费等）。 </div>
    <div class="t2 mt_40">第五条乙方权利义务</div>
    <div class="t3 mt_20">1.乙方需按合同约定为甲方提供本合同约定的服务，并对甲方的招聘和推广信息等进行维护。</div>
    <div class="t3 mt_20">2.乙方应保证提供服务的网站正常运行，如有技术调整将影响网站的服务超过24小时的，需提前通知甲方。</div>
    <div class="t3 mt_20">3.服务期内，如果甲方信息被投诉并经乙方证实甲方提供的信息违反本合同第四条之规定的，乙方有权停止为甲方提供服务。</div>
    <div class="t3 mt_20">
        4.服务期内，如果甲方信息、材料、商标、字号、域名、账号及密码等被乙方未经许可或超过允许范围而擅自使用或泄露、提供、许可第三方使用、利用的，甲方有权解除合同，乙方除返还已收取款项外，还需承担违约、赔偿责任。
    </div>
    <div class="t3 mt_20">5.乙方应保证甲方能够正常的发布广告和招聘信息，如网站不能正常发布，则服务时间相应顺延，如连续超过7日以上不能正常发布的，甲方有权终止合同，乙方需按剩余天数返还甲方已支付的费用。
    </div>
    <div class="t2 mt_40">第六条保密条款</div>
    <div class="t3 mt_20">
        1.任何一方对于因签署或履行本协议而了解或接触到的对方的资料、信息（包括但不限于商业秘密、经营信息、图文数据、与业务有关的客户资料、本协议的存在及涉及的付款金额、付款方式等）均负有保密义务：非经对方书面同意，任何一方不得向第三方泄露、给予或转让该等保密信息。
    </div>
    <div class="t3 mt_20">2.本条款不因协议的终止而无效，协议终止后对双方仍具有约束力。</div>
    <div class="t2 mt_40">第七条知识产权</div>
    <div class="t3 mt_20">1.为服务需求，乙方有权在其网站使用包括甲方的名称、商标、标识、网站名称以及网址，甲方营业执照登载信息或者电子链接标识等信息；非经甲方同意，乙方不得挪作他用。</div>
    <div class="t3 mt_20">
        2.甲方提供给乙方的信息和材料的所有权和知识产权归甲方所有，且甲方应保障提供的信息不侵犯任何第三人的权利；否则由甲方承担所有侵权责任和后果，乙方有权停止侵权信息的发布，且乙方因此遭受损失的，甲方需赔偿乙方的损失。
    </div>
    <div class="t3 mt_20">3.由乙方加工制作编辑润色完成并发布于乙方网站的全部内容的所有权和知识产权归乙方所有，涉及甲方的服务信息，甲方可转载使用。</div>
    <div class="t2 mt_40">第八条合同变更转让和终止</div>
    <div class="t3 mt_20">1.经双方协商一致，可对本协议内容以书面形式进行变更或补充。</div>
    <div class="t3 mt_20">2.非经另一方许可，任何一方不可将本协议项下的权利义务转让或分包给第三方。 </div>
    <div class="t3 mt_20">3.发生了本合同约定的可提前解除合同的情形或经甲乙双方协商一致的，一方可提前解除合同， 否则任何一方不可提前解除合同。</div>
    <div class="t2 mt_40">第九条不可抗力</div>
    <div class="t3 mt_20">不可抗力事件（包括但不限于地震、火灾、战争、政府行为等）造成乙方无需或无法继续向甲方提供约定服务的，乙方应向甲方退还已支付尚未履行的费用。</div>
    <div class="t2 mt_40">第十条违约责任</div>
    <div class="t3 mt_20">1.甲方违反本合同约定的，乙方有权解除合同，已支付尚未履行的费用不予退还。且如因甲方违约造成乙方损失的，甲方还需足额赔偿乙方的损失。 </div>
    <div class="t3 mt_20">2.乙方违反本合同约定，无法为甲方提供合同约定的服务的，甲方有权提前解除合同，乙方需退还甲方已支付但尚未履约的费用，且如因乙方违约造成甲方损失的，乙方还需足额赔偿甲方的损失。
    </div>
    <div class="t3 mt_20">3.非因本合同约定的情况，甲方要求提前解除合同的，已付未履行费用不予退还</div>
    <div class="t3 mt_20">4.非因本合同约定的情况，乙方要求提前解除合同或连续7天或以上无法提供服务的，需退还甲方已付未履行的费用且需按合同总额10%支付违约金。 </div>
    <div class="t2 mt_40">第十一条争议解决</div>
    <div class="t3 mt_20">因本合同而引起的争议或纠纷的，乙方与甲方应本着友好合作原则协商一致，如果协商解决不成，任何一方均有权向甲方或乙方所在地人民法院提起诉讼。</div>
    <div class="t2 mt_40">第十二条其它约定</div>
    <div class="t3 mt_20">1.本合同中乙方与甲方的联系方式包括但不限于：书面文件、电子邮件、传真、QQ、短信、微信等方式。乙方与甲方通过上述方式沟通或确认的事项予以确认和承认。 </div>
    <div class="t3 mt_20">2.本合同一式贰份，乙方和甲方各一份，合同自双方签字盖章之日起生效。</div>
    <div class="t3 mt_20">（以下无正文）</div>
    <div class="t3 mt_20">甲 方：{{$model['b_company_name']}}  </div>
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

    table {
        border-collapse: collapse;
    }

    table th {
        width: 120px;
    }

    table tr td {
        text-align: center;
    }
</style>
<script type="text/javascript"></script>
</body>

</html>