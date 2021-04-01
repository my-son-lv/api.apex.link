<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCollectTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('collect', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('cid')->comment('企业id');
            $table->integer('mid')->comment('会员id');
            $table->tinyInteger('type')->default(1)->comment('类型  1收藏  2候选人');
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
        Schema::dropIfExists('collect');
    }
}
