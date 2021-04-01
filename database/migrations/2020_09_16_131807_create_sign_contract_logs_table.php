<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSignContractLogsTable extends Migration
{
    /**
     * 合同操作记录
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sign_contract_logs', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->nullable()->comment('操作人');
            $table->tinyInteger('type')->nullable()->comment('type 1运营后台操作  2企业操作 3系统');
            $table->string('info')->nullable()->comment('操作描述');
            $table->integer('sign_id')->nullable()->comment('签约id');
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
        Schema::dropIfExists('sign_contract_logs');
    }
}
