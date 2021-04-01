<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddUnionidToCompany extends Migration
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
            $table->string('unionid')->nullable()->comment('微信唯一标识');
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
            $table->dropColumn('unionid');
        });
    }
}
