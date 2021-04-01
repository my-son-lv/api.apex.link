<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCommTypeToMembersInfo extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('members_info', function (Blueprint $table) {
            //
            $table->tinyInteger('comm_type')->default(1)->comment('1微信 2Skype 3Whatsapp');
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
            //
            $table->tinyInteger('comm_type');
        });
    }
}
