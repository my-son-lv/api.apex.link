<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMembersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('members', function (Blueprint $table) {
            $table->increments('id');
            $table->string('email')->comment('邮箱');
            $table->string('password')->comment('用户密码');
            $table->string('nick_name')->comment('用户昵称');
            $table->string('user_id')->comment('用户编号');
            $table->string('sign_id')->comment('员工编号');
            $table->string('last_login_ip')->nullable()->comment('最后登录IP');
            $table->dateTime('last_login_time')->nullable()->comment('最后登录时间');
            $table->string('register_ip')->nullable()->comment('注册IP');
            $table->dateTime('register_time')->nullable()->comment('注册时间');
            $table->string('token')->nullable()->comment('token');
            $table->dateTime('token_expire_time')->nullable()->comment('登录过期时间');
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
        Schema::dropIfExists('members');
    }
}
