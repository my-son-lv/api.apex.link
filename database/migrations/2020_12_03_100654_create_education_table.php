<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEducationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('education', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('mid')->comment('外教用户id');
            $table->string('edu_start_time')->nullable()->comment('开始时间');
            $table->string('edu_end_time')->nullable()->comment('结束时间');
            $table->string('school')->nullable()->comment('学校');
            $table->string('major')->nullable()->comment('专业');
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
        Schema::dropIfExists('education');
    }
}
