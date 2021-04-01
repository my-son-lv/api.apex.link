<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateContractsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('contracts', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->nullable()->comment('合同名称');
            $table->string('path')->nullable()->comment('合同html模板路径');
            $table->string('sign_x_y')->nullable()->comment('合同平台签署位置x,y');
            $table->string('sign_user_x_y')->nullable()->comment('合同用户签署位置x,y');
            $table->string('contract_data')->nullable()->comment('合同数据json');
            $table->softDeletes();
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
        Schema::dropIfExists('contracts');
    }
}
