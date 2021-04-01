<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddShowToVip extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('vips', function (Blueprint $table) {
            //
            $table->tinyInteger('show')->default(1)->comment('是否显示 1是 2否');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('vips', function (Blueprint $table) {
            //
            $table->dropColumn('show');
        });
    }
}
