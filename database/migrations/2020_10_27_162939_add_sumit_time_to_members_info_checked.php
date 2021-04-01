<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSumitTimeToMembersInfoChecked extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('members_info_checked', function (Blueprint $table) {
            $table->dateTime('submit_time')->nullable()->comment('提交时间');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('members_info_checked', function (Blueprint $table) {
            $table->dropColumn('submit_time');
        });
    }
}
