<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSignContractsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sign_contracts', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('interview_id')->nullable()->comment('面试ID');
            $table->integer('cid')->nullable()->comment('企业ID');
            $table->integer('user_id')->nullable()->comment('发起人ID');
            $table->integer('contract_id')->nullable()->comment('合同模板ID');
            $table->string('name')->nullable()->comment('签约名称');
            $table->string('a_name')->nullable()->comment('经办人-甲方');
            $table->string('a_phone')->nullable()->comment('经办人-甲方-手机号');
            $table->string('b_name')->nullable()->comment('经办人-乙方');
            $table->string('b_phone')->nullable()->comment('经办人-乙方-手机号');
            $table->string('b_company_name')->nullable()->comment('经办人-乙方-公司名称');
            $table->dateTime('end_time')->nullable()->comment('截止时间');
            $table->string('memo')->nullable()->comment('备注');
            $table->string('contract_data')->nullable()->comment('合同数据json');
            $table->tinyInteger('status')->default(1)->comment('合同状态 1草稿 2待签署 3已完成 4已拒绝 5已逾期 6已撤回');
            $table->dateTime('start_date')->nullable()->comment('发起时间');
            $table->string('notice')->nullable()->comment('通知方式 1短信 2站内通知 多个以英文逗号分隔');
            $table->string('account_id')->nullable()->comment('e签宝 经办人accountId');
            $table->string('user_reg_id')->nullable()->comment('e签宝 经办人注册ID');
            $table->string('org_id')->nullable()->comment('e签宝 企业accountId');
            $table->string('org_reg_id')->nullable()->comment('e签宝 组织人注册ID');
            $table->string('flow_id')->nullable()->comment('e签宝 实名认证流程ID');
            $table->string('file_id')->nullable()->comment('e签宝 合同文件id');
            $table->string('info')->nullable()->comment('原因');
            $table->integer('pdf_file_id')->nullable()->comment('合同id');
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
        Schema::dropIfExists('sign_contracts');
    }
}
