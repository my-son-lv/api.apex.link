<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddWorkTypeToMembersInfo extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('members_info', function (Blueprint $table) {
            //
            $table->tinyInteger('job_type')->default(3)->comment('工作类型 1线下 2线上 3不限');
            $table->tinyInteger('job_work_type')->default(3)->comment('工作性质 1不限 2全职 3兼职');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('members_info', function (Blueprint $table) {
            $table->dropColumn('job_type');
            $table->dropColumn('job_work_type');
        });
    }
}
