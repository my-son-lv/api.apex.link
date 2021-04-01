<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddUpFlgToInterview extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('interview', function (Blueprint $table) {
            //
            $table->tinyInteger('up_flg')->default(0)->comment('0正常 1变更');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('interview', function (Blueprint $table) {
            //
            $table->dropColumn('up_flg');
        });
    }
}
