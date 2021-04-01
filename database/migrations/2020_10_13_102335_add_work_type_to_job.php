<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddWorkTypeToJob extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('job', function (Blueprint $table) {
            $table->tinyInteger('work_type')->default(1)->comment('1不限 2全职 3兼职');
//            $table->tinyInteger('visa_ask')->default(1)->comment('状态 1工签 2其他');
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
            $table->dropColumn('work_type');
//            $table->dropColumn('visa_ask');
        });
    }
}
