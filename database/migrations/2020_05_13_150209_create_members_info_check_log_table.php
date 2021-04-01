<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMembersInfoCheckLogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('members_info_check_log', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('mid')->comment('用户id');
            $table->tinyInteger('status')->comment('审核状态 1审核通过 2审核驳回');
            $table->string('info')->comment('备注');
            $table->integer('uid')->comment('审核人');
            $table->tinyInteger('flg')->default(1)->comment('1未读 2已读');
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
        Schema::dropIfExists('members_info_check_log');
    }
}
