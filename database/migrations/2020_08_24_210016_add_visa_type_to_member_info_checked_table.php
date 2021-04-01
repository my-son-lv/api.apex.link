<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddVisaTypeToMemberInfoCheckedTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //

        Schema::table('members_info_checked', function (Blueprint $table) {
            $table->tinyInteger('in_domestic')->default(0)->comment('在国内 0不在 1在');
            $table->tinyInteger('visa_type')->nullable()->comment('签证类型 在国内是有效 1 Z  2 M 3 F 4 X 5 others');
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
            //
            $table->dropColumn('in_domestic');
            $table->dropColumn('visa_type');
        });
    }
}
