<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSchooleTypeToMembersInfoChecked extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('members_info_checked', function (Blueprint $table) {
            $table->integer('school_type')->nullable()->comment('1培训机构  2公立学校 3私立学校 4中介机构 5幼儿有 6其他');
            $table->integer('work_type')->nullable()->comment('1全职 2兼职');
            $table->string('student_age')->nullable()->comment('学生年龄 1 1-4岁 2 5-9岁 3 9-13岁 4 14-17岁 5 18岁以上 多个英文逗号分隔');
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
            $table->dropColumn('school_type');
            $table->dropColumn('work_type');
            $table->dropColumn('student_age');
        });
    }
}
