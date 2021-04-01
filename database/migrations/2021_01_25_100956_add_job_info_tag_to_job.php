<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddJobInfoTagToJob extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('job', function (Blueprint $table) {
            //
            $table->string('benefits_tag')->nullable()->comment('福利 标签 多个英文逗号分隔  1Annual Bonus 2Insurance 3Apartment 4HouseAllowance 5Flight allowance ');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('job', function (Blueprint $table) {
            //
            $table->dropColumn('benefits_tag');
        });
    }
}
