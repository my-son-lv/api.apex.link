<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateJobTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('job', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('cid')->comment("企业ID");
            $table->string('name')->comment('职位名称');
            $table->tinyInteger('type')->default(1)->comment('1线下 2线上 3不限');
            $table->integer('first_language')->defualt(0)->comment('母语 0不限 1母语 2非母语');
            $table->tinyInteger('sex')->default(0)->comment('性别 0不限 1男 2女');
            $table->integer('colour')->default(0)->comment('肤色 0不限 1白色');
            $table->integer('job_city')->nullable()->comment('工作地');
            $table->tinyInteger('language')->default(1)->comment('语言 1英语');
            $table->tinyInteger('job_type')->default(1)->comment('1学前语言 2中小学教育 3成人教育 4出国培训 5其他');
            $table->integer('job_week_day')->nullable()->comment('一周工作几天 1 1天；2 2天； 3 3天；4 4天；5 5天；6 5天以上 废除');
            $table->string('job_day_time')->nullable()->comment('一周工作几个小时 1-40之间 1,40');
            $table->tinyInteger('pay_type')->default(1)->comment('1月薪 2时薪 3待定 20201013只按月薪走');
            $table->string('pay')->nullable()->comment('金额 区间例：15,20 英文逗号分隔 单位K');
            $table->integer('pay_unit')->nullable()->comment('单位 12 13 月数');
            $table->tinyInteger('money_type')->default(1)->comment('1人民币  2美元');
            $table->tinyInteger('edu_type')->nullable()->comment('1 本科及以上 2 研究生及以上 3 博士及以上 4 不限');
            $table->string('cert')->nullable()->comment('证书  英文逗号分隔 1 TEFL 2 TESOL 3 TESL 4 CELTA');
            $table->integer('job_year')->nullable()->comment('工作年限 1 1年以内； 2 1-3年；3 3-5年；4 5-10年；5 10年以上');
            $table->integer('num')->default(0)->comment('招聘人数');
            $table->string('start_time')->nullable()->comment('招聘开始时间');
            $table->string('end_time')->nullable()->comment('招聘结束时间');
            $table->text('benefits')->nullable()->comment('福利待遇');
            $table->text('job_info')->nullable()->comment('工作介绍');
            $table->text('memo')->nullable()->comment('备注');
            $table->tinyInteger('status')->default(1)->comment('状态 1发布 2关闭');
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
        Schema::dropIfExists('job');
    }
}
