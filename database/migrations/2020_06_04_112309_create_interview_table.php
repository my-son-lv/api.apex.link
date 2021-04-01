<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateInterviewTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('interview', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('mid')->comment('外教id');
            $table->integer('cid')->comment('企业id');
            $table->dateTime('inte_time')->comment("面试时间");
            $table->tinyInteger('status')->default(0)->comment("状态 0待确认=已约面  1待面试 2面试中 3待签约 4已录用 5未通过 6已结束 7已取消 8已过期");
            $table->tinyInteger('eval_flg')->default(0)->comment('0待评价 1已评价 默认0');
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
        Schema::dropIfExists('interview');
    }
}
