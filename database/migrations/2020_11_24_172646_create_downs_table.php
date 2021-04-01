<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDownsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('downs', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('cid')->comment('企业id');
            $table->integer('mid')->comment('外教mid');
            $table->integer('vip_id')->nullable()->comment('会员id');
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
        Schema::dropIfExists('downs');
    }
}
