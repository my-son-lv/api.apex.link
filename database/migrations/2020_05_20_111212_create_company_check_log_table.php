<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCompanyCheckLogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('company_check_log', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('cid')->comment('公司id');
            $table->dateTime('submit_time')->comment('提交时间');
            $table->integer('check')->default(0)->comment('状态 0待审 1通过 2驳回');
            $table->string('info')->nullable()->comment('审核描述');
            $table->tinyInteger('is_read')->default(0)->comment('1未读 2已读');
            $table->integer('uid')->nullable()->comment('操作员id');
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
        Schema::dropIfExists('company_check_log');
    }
}
