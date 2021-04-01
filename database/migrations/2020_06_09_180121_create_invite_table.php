<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateInviteTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('invite', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('mid')->nullable()->comment('会员id');
            $table->integer('jid')->comment('职位id');
            $table->string('name')->comment('姓名');
            $table->string('phone')->comment('手机号');
            $table->string('area_code')->comment('区号');
            $table->string('email')->comment('邮箱');
            $table->string('code')->comment('邀请码');
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
        Schema::dropIfExists('invite');
    }
}
