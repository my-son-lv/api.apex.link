<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWxArticleEnsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wx_article_ens', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title')->nullable()->comment('文章标题');
            $table->string('desc')->nullable()->comment('文章描述');
            $table->string('thumb_url')->nullable()->comment('文章缩略图');
            $table->string('url')->nullable()->comment('文章链接');
            $table->integer('page')->nullable()->comment('所属page');
            $table->dateTime('time')->nullable()->comment('文章发表时间');
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
        Schema::dropIfExists('wx_article_ens');
    }
}
