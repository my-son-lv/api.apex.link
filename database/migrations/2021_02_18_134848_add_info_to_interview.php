<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddInfoToInterview extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('interview', function (Blueprint $table) {
            //
            $table->text('info')->nullable()->comment('面试拒绝原因');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('interview', function (Blueprint $table) {
            //
            $table->dropColumn('info');
        });
    }
}
