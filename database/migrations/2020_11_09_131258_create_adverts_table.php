<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAdvertsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('adverts', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title')->nullable()->comment('标题');
            $table->dateTime('start_time')->nullable()->comment('开始时间');
            $table->dateTime('end_time')->nullable()->comment('开始时间');
            $table->string('type')->nullable()->comment('类型 1小程序广告屏 2小程序banner位置 3PC广告弹屏 4PCbanner位置 多个英文逗号分隔');
            $table->tinyInteger('status')->default(1)->comment('状态 1正常 2关闭');
            $table->integer('img1')->nullable()->comment('图片id1');
            $table->string('url1')->nullable()->comment('跳转url1');
            $table->integer('img2')->nullable()->comment('图片id2');
            $table->string('url2')->nullable()->comment('跳转url2');
            $table->integer('img3')->nullable()->comment('图片id3');
            $table->string('url3')->nullable()->comment('跳转url3');
            $table->integer('img4')->nullable()->comment('图片id4');
            $table->string('url4')->nullable()->comment('跳转url4');
            $table->softDeletes();
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
        Schema::dropIfExists('adverts');
    }
}
