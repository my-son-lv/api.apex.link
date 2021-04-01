<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateVipActionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vip_actions', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('cid')->comment('企业id');
            $table->integer('vip_id')->comment('vip表id');
            $table->integer('pay')->comment('实际收入 元');
            $table->string('operator')->nullable()->comment('经办人');
            $table->string('operator_tel')->nullable()->comment('经办人电话');
            $table->string('type')->nullable()->comment('收款方式');
            $table->string('payee')->nullable()->comment('收款人');
            $table->date('pay_date')->nullable()->comment('收款时间');
            $table->string('memo')->nullable()->comment('备注');
            $table->tinyInteger('status')->default(1)->comment('1未生效 2正常 3已关闭 4已过期');
            $table->dateTime('start_time')->nullable()->comment('会员开始时间');
            $table->dateTime('end_time')->nullable()->comment('会员结束时间');
            $table->integer('yy_job_num')->default(0)->comment('会员职位已用发布数');
            $table->integer('yy_top')->default(0)->comment('会员已用置顶数');
            $table->integer('yy_down')->default(0)->comment('会员已用下载数');
            $table->integer('user_id')->nullable()->comment('操作人');
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
        Schema::dropIfExists('vip_actions');
    }
}
