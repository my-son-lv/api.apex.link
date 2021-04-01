<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWorkexperiencesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('workexperiences', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('mid')->comment('外教mid');
            $table->string('start_time')->nullable()->comment('工作开始时间');
            $table->string('end_time')->nullable()->comment('工作结束时间');
            $table->tinyInteger('now')->default(1)->comment('至今 1否 2是');
            $table->string('company_name')->nullable()->comment('公司名称');
            $table->string('position')->nullable()->comment('职位');
            $table->longText('work_desc')->nullable()->comment('工作介绍');
            $table->string('show')->nullable()->comment('前端用 不做任何处理');
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
        Schema::dropIfExists('workexperiences');
    }
}
