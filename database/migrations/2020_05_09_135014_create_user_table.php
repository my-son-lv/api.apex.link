<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->comment('用户姓名');
            $table->string('email')->comment('邮箱');
            $table->string('phone')->comment('手机号');
            $table->string('password')->comment('密码');
            $table->tinyInteger('type')->default(0)->comment('0平台用户');
            $table->tinyInteger('status')->default(0)->comment('0正常 1禁用 默认0');
            $table->string('last_login_ip')->nullable()->comment('最后登录IP');
            $table->dateTime('last_login_time')->nullable()->comment('最后登录时间');
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
        Schema::dropIfExists('user');
    }
}
