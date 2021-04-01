<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOfficialsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
//{"subscribe":1,"openid":"oqbHh5iAoUDFzhqiaZwcDl9XNHMM","nickname":"Justin","sex":2,"language":"zh_CN","city":"","province":"","country":"\u963f\u5bcc\u6c57","headimgurl":"http:\/\/thirdwx.qlogo.cn\/mmopen\/QMHVW5vMM9SK5Bted2tTfb20KmfzPxUpIx8fR5QQicGMO2iaffkcYs7Q95LYvuEfJY4sEF35g8dicYt8DuEfvSGXgNTBtu1MznH\/132","subscribe_time":1608798446,"unionid":"oQEcTwto4SGkbr5brLIDglPZlIBY","remark":"","groupid":0,"tagid_list":[],"subscribe_scene":"ADD_SCENE_QR_CODE","qr_scene":0,"qr_scene_str":""}
    public function up()
    {
        Schema::create('officials', function (Blueprint $table) {
            $table->increments('id');
            $table->string('openid')->nullable()->comment('微信open_id');
            $table->string('nickname')->nullable()->comment('用户的昵称');
            $table->tinyInteger('sex')->nullable()->comment('性别 1男 2女');
            $table->string('city')->nullable()->comment('城市');
            $table->string('province')->nullable()->comment('省份');
            $table->string('country')->nullable()->comment('国家');
            $table->string('headimgurl')->nullable()->comment('用户头像');
            $table->string('unionid')->nullable()->comment('只有在用户将公众号绑定到微信开放平台帐号后，才会出现该字段');
            $table->string('subscribe_scene')->nullable()->comment('来源，ADD_SCENE_SEARCH 公众号搜索，ADD_SCENE_ACCOUNT_MIGRATION 公众号迁移，ADD_SCENE_PROFILE_CARD 名片分享，ADD_SCENE_QR_CODE 扫描二维码，ADD_SCENE_PROFILE_LINK 图文页内名称点击，ADD_SCENE_PROFILE_ITEM 图文页右上角菜单，ADD_SCENE_PAID 支付后关注，ADD_SCENE_WECHAT_ADVERTISEMENT 微信广告，ADD_SCENE_OTHERS 其他');
            $table->tinyInteger('status')->default(1)->comment('状态 1关注 2取消关注');
            $table->dateTime('time')->nullable()->comment('关注时间/取消关注事件');
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
        Schema::dropIfExists('officials');
    }
}
