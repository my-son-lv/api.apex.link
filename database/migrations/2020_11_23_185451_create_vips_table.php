<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateVipsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vips', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->nullable()->comment('会员名称');
            $table->integer('money')->default(0)->comment('会员费用');
            $table->integer('month')->defaut(0)->comment('有效时长 月');
            $table->integer('job_num')->defualt(0)->comment('职位数');
            $table->integer('top')->default(0)->comment('置顶数');
            $table->integer('down')->default(0)->comment('下载简历数');
            $table->tinyInteger('status')->default(1)->comment('状态 1开启  2关闭');
            $table->string('visa_coupon')->nullable()->comment('签证优惠');
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
        Schema::dropIfExists('vips');
    }
}
