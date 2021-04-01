<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCountryToMembersInfoCheckedTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('members_info_checked', function (Blueprint $table) {
            $table->integer('country')->nullable()->commet('当前所在国家');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('members_info_checked', function (Blueprint $table) {
            $table->dropColumn('country');
        });
    }
}
