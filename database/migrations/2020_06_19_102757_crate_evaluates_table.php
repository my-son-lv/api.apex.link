<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CrateEvaluatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('evaluates', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('iid')->comment('面试记录id');
            $table->integer('cid')->comment('企业用户id');
            $table->integer('mid')->comment('会员用户id');
            $table->integer('all')->comment('总体评分');
            $table->integer('qualities')->comment('形象气质 评分');
            $table->integer('skill')->comment('专业技能评分');
            $table->integer('info')->comment('沟通表达评分');
            $table->text('memo')->comment('评价');
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
        //
        Schema::dropIfExists('evaluates');
    }
}
