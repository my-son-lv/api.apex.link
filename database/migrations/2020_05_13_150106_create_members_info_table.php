<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMembersInfoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('members_info', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('mid')->comment('用户id');
            $table->string('name')->nullable()->comment('真实姓名');
            $table->string('last_name')->nullbale()->comment('last name');
            $table->tinyInteger('sex')->default(0)->comment('性别  0男 1女 默认0男');
            $table->date('brithday')->nullable()->comment('生日');
            $table->integer('nationality')->nullable()->comment('国籍');
            $table->string('abroad_address')->nullable()->comment('国外地址');
            $table->integer('china_address')->nullable()->comment('国内地址');
            $table->string('school')->nullable()->comment('大学');
            $table->integer('university')->nullable()->comment('学历');
            $table->string('phone')->nullable()->comment('手机号');
            $table->string('wechat')->nullable()->comment('微信');
            $table->string('contact_name')->nullable()->comment('紧急联系人姓名');
            $table->string('contact_phone')->nullable()->comment('紧急联系人电话');
            $table->string('passport')->nullable()->comment('护照');
            $table->string('major')->nullable()->comment('专业');
            $table->integer('working_seniority')->nullable()->comment('工作年限');
            $table->string('working_city')->nullable()->comment('期望工作地');
            $table->integer('work_flg')->default(0)->comment('0否 1是 默认0');
            $table->date('work_start_time')->nullable()->comment('工作开始时间');
            $table->date('work_end_time')->nullable()->comment('工作结束时间');
            $table->text('desc')->nullable()->comment('个人简介');
            $table->string('videos')->nullable()->comment('视频列表 逗号分隔多条');
            $table->string('photos')->nullable()->comment('照片列表 逗号分隔多条');
            $table->tinyInteger('edu_cert_flg')->default(0)->comment('教育证书 0无 1有');
            $table->string('edu_cert_imgs')->nullable()->comment('教育证书图片列表 多个逗号隔开');
            $table->tinyInteger('edu_auth_flg')->default(0)->comment('教育认证 0无 1有');
            $table->string('edu_auth_imgs')->nullable()->comment('教育认证图片列表 多个逗号分隔');
            $table->tinyInteger('work_visa_flg')->default(0)->comment('工作签证 0无 1有');
            $table->tinyInteger('science_flg')->default(0)->comment('学术认证 0无 1有');
            $table->tinyInteger('commit_flg')->default(0)->comment('犯罪记录 0无 1有');
            $table->integer('notes')->nullable()->comment('简历文件id');
            $table->integer('pay_type')->default(1)->comment('薪资类型  1 10k-13k 2 13k-16k 3 16k-20k  4 20k-25k   5  >25k ');
            $table->integer('hot')->default(0)->comment('热度 默认0');
            $table->tinyInteger('sign_status')->default(0)->comment('签约 0待签约 1已签约');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('members_info');
    }
}
