<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddOppenId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('companys', function (Blueprint $table) {
            //
            $table->string('open_id')->nullable()->comment('小程序open_id');
            $table->string('session_key')->nullable()->comment('session_key 小程序');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('companys', function (Blueprint $table) {
            //
            $table->dropColumn('open_id');
            $table->dropColumn('session_key');
        });
    }
}
