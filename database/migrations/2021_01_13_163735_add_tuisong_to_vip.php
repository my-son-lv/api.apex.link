<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTuisongToVip extends Migration
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
            $table->string('tuisong')->nullable()->comment('推送');
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
            $table->dropColumn('tuisong');
        });
    }
}
