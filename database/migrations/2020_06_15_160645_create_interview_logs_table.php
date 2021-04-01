<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateInterviewLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('interview_logs', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('vid')->comment('面试记录id');
            $table->tinyInteger('type')->defualt(1)->comment('1企业操作 2顾问操作 3外教操作');
            $table->string('info')->comment('描述');
            $table->dateTime('time')->comment('时间');
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
        Schema::dropIfExists('interview_logs');
    }
}
