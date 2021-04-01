<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRoomsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('rooms', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('inter_id')->comment('面试id');
            $table->tinyInteger('admin_flg')->default(1)->comment('顾问 默认1 1未进入 2已进入');
            $table->tinyInteger('company_flg')->default(1)->comment('企业 默认1 1未进入 2已进入');
            $table->tinyInteger('teach_flg')->default(1)->comment('外教 默认1 1未进入 2已进入');
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
        Schema::dropIfExists('rooms');
    }
}
