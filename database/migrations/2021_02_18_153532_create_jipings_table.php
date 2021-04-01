<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateJipingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('jipings', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('cid')->comment('企业ID');
            $table->integer('jid')->comment('职位ID');
            $table->integer('vip_id')->comment('vip记录ID');
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
        Schema::dropIfExists('jipings');
    }
}
