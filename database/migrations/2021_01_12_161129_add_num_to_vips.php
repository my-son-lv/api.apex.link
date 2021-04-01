<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddNumToVips extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('vips', function (Blueprint $table) {
            //
            $table->integer('jiping')->default(0)->comment('急聘');
            $table->integer('yaoqing')->default(0)->comment('应聘邀请');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('vips', function (Blueprint $table) {
            //
            $table->dropColumn('jiping');
            $table->dropColumn('yaoqing');
        });
    }
}
