<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateApplicationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('applications', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('cid')->nullable()->comment('企业id');
            $table->integer('vip_id')->comment('会员类型id');
            $table->string('company_name')->nullable()->comment('公司名称');
            $table->string('company_tel')->nullable()->comment('公司电话');
            $table->string('ip')->nullable()->comment('申请者IP');
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
        Schema::dropIfExists('applications');
    }
}
