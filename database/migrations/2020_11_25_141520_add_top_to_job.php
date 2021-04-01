<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTopToJob extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('job', function (Blueprint $table) {
            //
            $table->integer('top')->default(0)->comment('置顶 0否 1是');
            $table->dateTime('top_exp_time')->nullable()->comment('置顶过期时间');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('job', function (Blueprint $table) {
            //
            $table->dropColumn('top');
            $table->dropColumn('top_exp_time');
        });
    }
}
