<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('api/teach/makeRecommPlaybill','Admin\MembersInfoController@makeRecommPlaybill');
Route::get('api/job/makeRecommPlaybill','Admin\JobController@makeRecommPlaybill');
//合同pdf预览
Route::get("contractTmp","Admin\SignContractController@contractTmp");
//百度地图 查询幼儿园用
Route::get('baidu','Test\TestController@baidu');
//高德地图 查询幼儿园用
Route::get('gaode','Test\TestController@gaode');
//小程序修改show
Route::get('xcxClose','Test\TestController@xcxClose');
//过年红包封面
Route::get('year','Test\TestController@Year');


Route::group(['namespace' => 'AdminTmp', 'prefix' => '/admin'], function () {
    Route::get('/',function (){
        exit('已停用,请进入运营后台');
    });
    Route::get('/login',function (){
        exit('已停用,请进入运营后台');
    });
//    Route::get('login','LoginController@login')->name('admin.login');
//    Route::post('login','LoginController@loginCheck')->name('admin.login');
});


Route::group(['namespace' => 'AdminTmp', 'prefix' => '/admin' , 'middleware' => 'webAdminCheckLogin' ], function () {
    Route::get('main', 'MainController@index')->name('admin.main');
    Route::get('logout', 'LoginController@logout')->name('admin.logout');
});

Route::group(['namespace' => 'AdminTmp', 'prefix' => '/admin' , 'middleware' => ['webAdminCheckLogin','webAdminCheckRole'] ], function () {
    #外教
    Route::group(['prefix'   => 'member'],function (){
        Route::get('add','MemberController@add')->name('admin.member.add');
        Route::post('add','MemberController@addSave')->name('admin.member.add');
    });

    #企业
    Route::group(['prefix'   => 'company'],function (){
        Route::get('index','CompanyController@index')->name('admin.company.index');

        Route::get('add','CompanyController@add')->name('admin.company.add');
        Route::post('add','CompanyController@addSave')->name('admin.company.add');
    });

    #推广管理
    Route::group([ 'prefix' => '/publicity'], function () {
        Route::get('index','PublicityController@index')->name('admin.publicity.index');

        Route::get('add','PublicityController@add')->name('admin.publicity.add');
        Route::post('add','PublicityController@addSave')->name('admin.publicity.add');
        Route::get('edit','PublicityController@edit')->name('admin.publicity.edit');
        Route::post('edit','PublicityController@editSave')->name('admin.publicity.edit');
        Route::post('show','PublicityController@show')->name('admin.publicity.show');
        Route::post('del','PublicityController@del')->name('admin.publicity.del');
    });

    #用户
    Route::group([ 'prefix' => '/user'], function () {
        Route::get('index','UserController@index')->name('admin.user.index');

        Route::get('add','UserController@add')->name('admin.user.add');
        Route::post('add','UserController@addSave')->name('admin.user.add');

        Route::get('edit','UserController@edit')->name('admin.user.edit');
        Route::post('edit','UserController@editSave')->name('admin.user.edit');

        Route::post('status','UserController@status')->name('admin.user.status');
        Route::post('del','UserController@del')->name('admin.user.del');

        Route::post('upPwd','UserController@upPwd')->name('admin.user.upPwd.white');
    });
    #角色
    Route::group([ 'prefix' => '/role'], function () {
        Route::get('index','RoleController@index')->name('admin.role.index');

        Route::get('add','RoleController@add')->name('admin.role.add');
        Route::post('add','RoleController@addSave')->name('admin.role.add');

        Route::get('edit','RoleController@edit')->name('admin.role.edit');
        Route::post('edit','RoleController@editSave')->name('admin.role.edit');

        Route::post('status','RoleController@status')->name('admin.role.status');
        Route::post('del','RoleController@del')->name('admin.role.del');
    });
    #菜单
    Route::group([ 'prefix' => '/menu'], function () {
        Route::get('index','MenuController@index')->name('admin.menu.index');

        Route::get('add','MenuController@add')->name('admin.menu.add');
        Route::post('add','MenuController@addSave')->name('admin.menu.add');

        Route::get('edit','MenuController@edit')->name('admin.menu.edit');
        Route::post('edit','MenuController@editSave')->name('admin.menu.edit');

        Route::post('status','MenuController@status')->name('admin.menu.status');
        Route::post('del','MenuController@del')->name('admin.menu.del');

        Route::post('menuList','MenuController@menu_list')->name('admin.menu.menuList.white');
    });


    Route::group(['prefix' => '/public'],function (){
        Route::any('getCityZtree','PublicController@getCityZtree')->name('admin.public.getCityZtree');
    });
    Route::group(['prefix' => '/uploads'],function (){
        Route::post('uploadsImages','UploadsController@uploadImages');
        Route::post('delImages','UploadsController@delImages');

    });

});