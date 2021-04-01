<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateInterviewLogs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('interview_logs', function (Blueprint $table) {
            //
            $table->string('info1')->nullable()->comment('运营后台进度');
            $table->dropColumn('type');
            $table->dropColumn('time');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('interview_logs', function (Blueprint $table) {
            //
            $table->dropColumn('info1');
        });
    }
}
