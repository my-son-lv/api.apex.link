<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCompanysTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('companys', function (Blueprint $table) {
            $table->increments('id');
            $table->string('phone')->comment('用户手机号');
            $table->string('password')->comment('用户密码');
            $table->string('company_name')->nullable()->comment('公司名称');
            $table->string('business_name')->nullable()->comment('企业名称');
            $table->string('business_flg')->default(0)->comment('营业执照 0无 1有');
            $table->string('business_img')->nullable()->comment('营业执照多个用逗号分隔');
            $table->tinyInteger('type')->nullable()->comment('机构类型 1培训学校  2幼儿园 3国际学校 4公立学校 5在线课程 6其他');
            $table->integer('city')->nullable()->comment('城市id');
            $table->string('address')->nullable()->comment('详细办公地址');
            $table->tinyInteger('talent')->nullable()->comment('资质 0不具备 1具备');
            $table->string('talent_img')->nullable()->comment('资质照片');
            $table->string('student_age')->nullable()->comment('学生年龄 1 0-3岁 2 3-6岁 3 7-12岁 4 13-18岁 5 18岁以上  多个以英文逗号分隔');
            $table->integer('abroad_staff')->nullable()->comment('外籍员工数');
            $table->integer('needs_num')->nullable()->comment('年度需求外籍员工数 1 1-10人 2 11-20人 3 21-50人  4 51人以上');
            $table->integer('pay')->nullable()->comment('月均薪资(税后) 1 15000以下 2 15000-20000 3 20000以上');
            $table->string('contact')->nullable()->comment('联系人');
            $table->string('contact_phone')->nullable()->comment('联系电话');
            $table->string('work_email')->nullable()->comment('工作邮箱');
            $table->string('school_img_1')->nullable()->comment('校区图片 多张  1');
            $table->string('school_img_2')->nullable()->comment('校区图片 多张 2');
            $table->string('logo')->nullable()->comment('公司logo');
            $table->string('last_login_ip')->nullable()->comment('最后登录IP');
            $table->dateTime('last_login_time')->nullable()->comment('最后登录时间');
            $table->string('register_ip')->nullable()->comment('注册IP');
            $table->dateTime('register_time')->nullable()->comment('注册时间');
            $table->string('token')->nullable()->comment('token');
            $table->dateTime('token_expire_time')->nullable()->comment('登录过期时间');
            $table->integer('status')->default(0)->comment('状态 0新用户 1待审 2审核通过 3驳回');
            $table->integer('check_log_id')->nullable()->comment('提交审核id');
            $table->integer('submit_num')->default(0)->comment('提交次数');
            $table->tinyInteger('gw_flg')->default(1)->comment('顾问 1无 2有');
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
        Schema::dropIfExists('companys');
    }
}
