<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddVisaExpDateToMembersInfoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('members_info', function (Blueprint $table) {
            $table->date('visa_exp_date')->nullable()->commet('签证过期时间');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('members_info', function (Blueprint $table) {
            $table->dropColumn('visa_exp_date');
        });
    }
}
