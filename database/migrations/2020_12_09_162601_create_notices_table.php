<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNoticesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        /**
         * type 1入驻 2其他 3面试
         *
         * 1001外教注册
         * 1002企业注册
         * 1003外教提交了入驻申请
         * 1004企业提交了入驻申请
         * 1005xxx通过了外教的入驻申请
         * 1006xxx通过了企业的入驻申请
         * 1007xxx驳回了外教xxx入驻申请
         * 1008xxx驳回了企业xxx入驻申请
         * 1009外教xxx信息自动审核通过
         *
         * 2001企业xxx修改平台信息
         * 2002企业发布了招聘需求
         * 2003xxx修改了外教xxx平台信息
         * 2004xxx修改了企业xxx平台信息
         * 2005xxx为企业xxx添加了招聘需求
         * 2006xxx修改了企业xxx招聘需求
         * 2007xxx将外教xxx的顾问变更为xxx
         * 2008xxx将企业xxx的顾问变更为xxx
         * 2009xxx添加了外教xxx
         * 2010xxx添加了企业xxx
         * 2011企业用户XXX 申请购买会员 用户电话号13717721147，请及时与企业联系
         * 2012XXX企业 通过官网申请购买会员 用户电话号13717721147，请及时与企业联系
         *
         * 3001企业用户AAA(机构名称)预约了AAA(外教名称)于北京时间2020年8月8日 14:00进行面试
         * 3002企业用户AAA(机构名称)修改了北京时间2020年8月8日 14:00与AAA(外教名称)的面试，修改后时间为：北京时间2020年8月8日 14:00
         * 3003企业用户AAA(机构名称)取消了北京时间2020年8月8日 14:00与AAA(外教名称)的面试
         * 3004外教用户AAA(外教名称)想修改与AAA(机构名称)北京时间2020年8月8日 14:00的面试
         * 3005外教用户AAA(外教名称)想取消与AAA(机构名称)北京时间2020年8月8日 14:00的面试
         * 3006(顾问姓名) 同意了XXXX机构与xxx外教2020-07-21  16:20 的面试
         * 3007(顾问姓名) 拒绝了xxxx机构与xxxx外教2020-07-21  16:20 的面试
         * 3008(顾问姓名) 变更了xxxx机构与xxxx外教2020-07-21  16:20 的面试，变更后时间为2020-07-21  16:20
         * 3009(顾问姓名) 取消了xxxx机构与xxxx外教2020-07-21  16:20 的面试
         * 3010xxxx机构与xxxx外教2020-07-21  16:20 的面试已完成。结果为备选/未通过/待签约
         * 3011XXXX外教已被xxxx机构录用
         */
        Schema::create('notices', function (Blueprint $table) {
            $table->increments('id');
            $table->text('content')->nullable()->comment('通知内容');
            $table->integer('to_uid')->comment('发送给谁');
            $table->tinyInteger('type')->default(1)->comment('类型 1入驻 2其他 3面试');
            $table->integer('code')->nullable()->comment('类型细分');
            $table->tinyInteger('read_flg')->default(1)->comment('1未读 2已读');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('notices');
    }
}
