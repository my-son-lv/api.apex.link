<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSignContractUrgesTable extends Migration
{
    /**
     * 催办记录
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sign_contract_urges', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('sign_id')->comment('催办签约id');
            $table->integer('user_id')->comment('催办人');
            $table->string('notice')->comment('通知方式 1短信 2站内通知 多个以英文逗号分隔');
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
        Schema::dropIfExists('sign_contract_urges');
    }
}
