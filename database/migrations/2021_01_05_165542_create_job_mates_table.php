<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateJobMatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('job_mates', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('jid')->comment('职位id');
            $table->integer('mid')->comment('外教id');
            $table->integer('level')->comment('积分');
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
        Schema::dropIfExists('job_mates');
    }
}
