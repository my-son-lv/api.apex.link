<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateImUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('im_users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('user_id')->comment('外教id 企业id 管理后台id');
            $table->integer('type')->comment('类型 1外教 2企业 3管理后台');
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
        Schema::dropIfExists('im_users');
    }
}
