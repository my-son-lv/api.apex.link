<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddYyYaoqingToVipActions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('vip_actions', function (Blueprint $table) {
            //
            $table->integer('yy_jiping')->default(0)->comment('已用 急聘数');
            $table->integer('yy_yaoqing')->default(0)->comment('已用 邀请面试数');
            $table->integer('yy_tuisong')->default(0)->comment('已用 精准推送数');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('vip_actions', function (Blueprint $table) {
            //
            $table->dropColumn('yy_jiping');
            $table->dropColumn('yy_yaoqing');
            $table->dropColumn('yy_tuisong');
        });
    }
}
