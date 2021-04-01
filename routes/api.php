<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

//Route::middleware('auth:api')->get('/user', function (Request $request) {
//    return $request->user();
//});
//Route::any('test','Test\TestController@test');
Route::any('sendTempNotice',"WxNoticeController@sendTempNotice");

Route::group(['middleware' => 'cors'],function (){
    Route::any('createQrCode','Controller@createQrCode');
//    Route::any('test11','Test\TestController@testSendSms');
    Route::any('getTest','Test\TestController@getTest');
});
//官网
Route::group(['namespace' => 'Website','prefix' => 'website','middleware' => 'cors'],function (){
    Route::post('message','WebsiteController@message');
    Route::get('getArticleList','WebsiteController@getArticleList');
    Route::get('getEnArticleList','WebsiteController@getEnArticleList');

    Route::get('getJob','WebsiteController@getJob');
});

//异步通知
Route::group(['prefix' => 'eSign','middleware' => 'cors'],function (){
    Route::post('eSignAutoNotify','Admin\SignContractController@eSignAutoNotify');
});


//推广
Route::group(['namespace' => 'Market','prefix' => 'market','middleware' => 'cors'],function (){
    Route::any('register','InviteController@register');
    Route::any('sendSms','InviteController@sendSms');
    Route::any('checkAccount','InviteController@checkAccount');
    Route::any('inviteCount','InviteController@inviteCount');
});


//前台接口 教师会员
Route::group(['namespace' => 'Index','middleware' => 'cors'],function (){
    //无需token
    Route::group(['prefix' => 'index'], function () {
        //发送验证码
        Route::post('sendCode','LoginController@sendCode');
        //判断用户是否存在
        Route::post('isMemberExist','LoginController@isMemberExist');
        //用户注册
        Route::post('register','LoginController@register');
        //忘记密码
        Route::post('forgetPassword','LoginController@forgetPassword');
        //用户登录
        Route::any('login','LoginController@login');
        //获取国籍列表
        Route::get('getCountryList','CountryController@getCountryList');
        //文件上传
        Route::any('upload','UploadController@upload');
        //获取省份
        Route::post('getProvince','RegionController@getProvince');
        //获取城市
        Route::post('getCity','RegionController@getCity');
        //通过首字母获取城市
        Route::get('getCityByChar','RegionController@getCityByChar');
        //修改用户为新用户状态
//        Route::post('upStatusToNewUser','MemberController@upStatusToNewUser');
        //获取客服im账号
        Route::any('getImAdviser','MemberController@getImAdviser');

    });

    //需token
    Route::group(['prefix' => 'index' ,'middleware' => 'checkToken'], function () {
        //退出登录
        Route::post('logout','LoginController@logout');
        //手机端第一次提交
//        Route::post('firstMobileComment','MemberController@firstMobileComment');
        //手机端第二页提交
//        Route::post('twoMobileComment','MemberController@twoMobileComment');
        //手机端第三页提交
//        Route::post('threeMobileComment','MemberController@threeMobileComment');
        //手机端第四页提交
//        Route::post('fourMobileComment','MemberController@fourMobileComment');

        //外教入驻提交信息
        Route::post('infoComment','MemberController@infoComment');
        //获取外教草稿
        Route::get('getTeachCommentInfo','MemberController@getTeachCommentInfo');
        //获取外教详情（审核通过）
        Route::get('getTeachInfo','MemberController@getTeachInfo');
        //外教收藏取消职位 1收藏 2取消收藏
        Route::post('jobCollect','JobCollectController@jobCollect');
        //外教收藏列表
        Route::get('jobCollectList','JobCollectController@jobCollectList');
        //第一页 保存草稿 提交
        Route::post('firstComment','MemberController@firstComment');
        //第二页 保存草稿 提交
        Route::post('twoComment','MemberController@twoComment');
        //删除草稿
//        Route::post('delDraft','MemberController@delDraft');
        //获取第一页草稿信息
//        Route::post('getFirstInfo','MemberController@getFirstInfo');
        //获取第二页草稿信息
//        Route::post('getTwoInfo','MemberController@getTwoInfo');
        //通过token获取状态和名称
        Route::post('getStatusByToken','LoginController@getStatusByToken');
        //审核结果已读
        Route::post('checkRead','MemberController@checkRead');
        //取消审核
//        Route::post('cancelCheck','MemberController@cancelCheck');
        //详情接口
//        Route::post('desc','MemberController@view');
        //修改用户详情
        Route::post('updateInfo','MemberController@updateInfo');
        //上传简历
//        Route::post('addNotes','MemberController@addNotes');
        //删除简历
//        Route::post('delNotes','MemberController@delNotes');
        //职位申请
        Route::post('job/jobApplication','MemberController@jobApplication');

        //面试管理
        Route::group(['prefix' => 'interview'],function (){
            //列表
            Route::post('list','InerviewController@teachInterList');
            //列表
            Route::post('desc','InerviewController@teachInterDesc');
            //列表
            Route::post('my','InerviewController@myInterList');
            //面试进度
            Route::post('interSpeed','InerviewController@interSpeed');
            //面试日历
//            Route::post('myDayList','InerviewController@myDayList');
            //我的面试记录
            Route::post('myLogList','InerviewController@myLogList');
            //取消面试/修改面试
            Route::POST('teachUpdateInterview','InerviewController@teachUpdateInterview');
            //同意面试/拒绝面试
            Route::post('joinInterview','InerviewController@joinInterview');
        });
    });


});

//前台接口 企业会员
Route::group(['namespace' => 'Index','middleware' => 'cors'],function (){
    //无需token
    Route::group(['prefix' => 'company'], function () {
        //广告图片
        Route::any('advert','AdvertController@advert');
        //图片验证码
        Route::any('captcha','LoginController@captcha');
        //发送短信验证码
        Route::post('sendSms','LoginController@sendSms');
        //发送验证码（身份验证）
        Route::post('sendCheckSms','CompanyController@sendCheckSms');
        //手机号是否存在
        Route::post('isPhoneExist','CompanyController@isPhoneExist');
        //公司注册
        Route::post('companyRegister','LoginController@companyRegister');
        //公司登陆
        Route::post('companyLogin','LoginController@companyLogin');
        //找回密码
        Route::post('companyRestPassword','LoginController@companyRestPassword');
        //获取城市
        Route::any('getCity','RegionController@getCitys');

        //外教详情
        Route::post('teachDesc','CompanyController@teachDesc');
        //搜索外教
        Route::post('searchTeach','CompanyController@searchTeach');
        //购买会员
        Route::post('/vip/buy','ApplicationController@buy');
        //获取会员价格
        Route::post('getVipList','CompanyController@getVipList');
        //职位管理
        Route::group(['prefix'=>'job'],function (){
            //职位列表
            Route::any('allList','JobController@allList');
            //职位详情
            Route::post('desc','JobController@jobDesc');
            //置顶职位列表
            Route::get('topList','JobController@topList');
        });
    });

    //需token
    Route::group(['prefix' => 'company' ,'middleware' => 'checkCompanyToken'], function () {
        //微信授权
        Route::post('wxAuthInfo','CompanyController@wxAuthInfo');
        //通过token获取信息
        Route::post('getInfoByToken','LoginController@getInfoByToken');
        //退出登录
        Route::post('logoutCompany','LoginController@logoutCompany');
        //提交资料
        Route::post('submitCompany','CompanyController@submitCompany');
        //取消审核
        Route::post('cancelCheck','CompanyController@cancelCheck');
        //已读接口
        Route::post('checkRead','CompanyController@checkRead');

        //获取客服im账号
        Route::post('getImAdviser','CompanyController@getImAdviser');
        //修改手机号
        Route::post('checkPassword','CompanyController@checkPassword');
        Route::post('updatePhoneByCode','CompanyController@updatePhoneByCode');
        Route::post('getComapnyDesc','CompanyController@getComapnyDesc');
        //修改头像
        Route::post('updateLogo','CompanyController@updateLogo');

        //下载简历
        Route::post('/teach/downNotes','CompanyController@downNotes');
        //下载简历历史
        Route::get('/teach/downList','CompanyController@downList');

        //更换微信
        Route::post('updateWx','WxCompanyController@updateWx');

        //获取平台数据
        Route::get('/vip/getPlatData','VipController@getPlatData');


        //职位管理
        Route::group(['prefix'=>'job'],function (){
            //添加职位
            Route::post('add','JobController@addJob');
            //职位列表
            Route::any('list','JobController@jobList');
            //删除职位
            Route::post('del','JobController@jobDel');
            //关闭职位
            Route::post('close','JobController@jobClose');
            //更新职位
            Route::post('update','JobController@jobUpdate');
            //获取所有职位
            Route::get('getJobList','JobController@getJobList');
            //职位置顶
            Route::post('top','JobController@top');
            //精准推送
            Route::post('jingzhuntuisong','JobController@jingzhuntuisong');
            //急聘服务
            Route::post('jiping','JobController@jiping');

            //职位申请列表
            Route::post('application','JobApplicationController@jobAppList');
            //职位已读
            Route::post('JobAppRead','JobApplicationController@JobAppRead');
            //职位详情
//            Route::post('desc','JobController@jobDesc');
            //职位处理结果  //result 1未处理  2可以聊 3不合适 id:1
            Route::post('jobResult','JobApplicationController@jobResult');
        });
        #合同管理
        Route::group(['prefix' => 'contract'],function (){
            Route::get('list','ContractController@list');
            //预览
            Route::get("signView",'ContractController@signView');
        });


        //收藏 取消收藏   候选人 取消候选人
        Route::post('collect','CollectController@collect');
        //收藏  候选人 列表
        Route::post('collect/list','CollectController@list');

        //人才管理
        Route::group(['prefix'=>'inerview'],function (){
            Route::post('invite','InerviewController@invite');
            Route::post('cancelInvite','InerviewController@cancelInvite');
            Route::post('list','InerviewController@listInvite');
            Route::post('update','InerviewController@updateInvite');
            Route::post('xcx_daylist','InerviewController@xcx_daylist');
            Route::post('daylist','InerviewController@daylist');
            Route::post('logList','InerviewController@logList');
            Route::post('interviewDesc','InerviewController@interviewDesc');
            Route::post('interviewSpeed','InerviewController@interviewSpeed');
            //解散面试房间
            Route::post('closeRoome', 'InerviewController@closeRoome');
            /*Route::post('interviewEval','InerviewController@interviewEval');
            Route::post('interviewResult','InerviewController@interviewResult');*/

            //面试结果评价
            Route::post('resultInter','InerviewController@resultInter');
        });



    });
});

//三方公共接口
Route::group(['middleware' => 'cors'],function () {
    Route::post('intoRoom', 'RoomsController@intoRoom');
    Route::any('wxMessageNotice','WxNoticeController@wxMessageNotice');
    Route::any('wxGzhMessageNotice','WxNoticeController@wxGzhMessageNotice');

});

//后台接口
Route::group(['namespace' => 'Admin','middleware' => 'cors'],function (){

    //无需token
    Route::group(['prefix' => 'admin'],function (){
        //登录
        Route::post('login','LoginController@login');

        Route::group(['prefix' => 'public'],function (){
            //获取国籍列表
            Route::post('getNationList','PublicController@getNationList');
            //获取省市区
            Route::any('getCitys','PublicController@getCitys');
            //公司列表
            Route::any('getCompaanyList','PublicController@getCompaanyList');
            //合同列表
            Route::get('getContractList','ContractController@list');
            //通过面试获取合同信息
            Route::get('getInterContract','InterviewController@getInterContract');
            //获取平台日志
            Route::post('getTerraceLog','PublicController@getTerraceLog');
            //获取会员类型
            Route::get('getVipList','PublicController@getVipList');
            //获取所有公司开启状态的职位
            Route::get('jobOnList','PublicController@jobOnList');
        });


    });


    //需要token
    Route::group(['prefix' => 'admin','middleware' => 'checkAdminToken'],function(){
        //退出登录
        Route::post('logout','LoginController@logout');
        //通过TOKEN获取信息
        Route::post('getInfoByToken','LoginController@getInfoByToken');
        //获取聊天列表
        Route::post('getImUserList','ImUserController@getImUserList');

        #聊天常用语
        Route::group(['prefix' => 'message'],function(){
            //列表
            Route::get('list','MessageController@list');
            Route::post('store','MessageController@store');
            Route::post('update','MessageController@update');
            Route::get('show','MessageController@show');
            Route::post('destroy','MessageController@destroy');
        });
        #公司审核入驻
        Route::group(['prefix' => 'companyCheck'],function(){
            //列表
            Route::post('list','CompanyController@checkList');
            //详情
            Route::post('desc','CompanyController@checkDesc');
            //审核
            Route::post('check','CompanyController@check');
            //日志
            Route::post('checkLog','CompanyController@checkLog');
        });


        #企业管理
        Route::group(['prefix' => 'company'],function(){
            //企业添加
            Route::post('add','CompanyController@add');
            //企业编辑
            Route::post('edit','CompanyController@edit');
            //企业删除
            Route::post('delete','CompanyController@delete');
            //修改企业备注
            Route::post('updateMemo','CompanyController@updateMemo');
            //列表
            Route::post('list','CompanyController@list');
            //详情
            Route::post('desc','CompanyController@desc');
            //修改
            Route::post('updateCompany','CompanyController@updateCompany');
            //顾问
            Route::post('adviser','CompanyController@adviser');
            //顾问列表
            Route::any('adviserList','CompanyController@adviserList');
            //职位列表
            Route::post('jobList','JobController@jobList');

            //职位详情
            Route::post('jobDesc','JobController@jobDesc');
            //职位关闭
            Route::post('jobClose','JobController@jobClose');
            //职位添加
            Route::post('jobAdd','JobController@add');
            //职位编辑
            Route::post('jobEdit','JobController@edit');
            //职位删除
            Route::post('jobDelete','JobController@delete');
            //职位列表
            Route::post('jobIndex','JobController@index');
            //职位申请列表
            Route::post('jobAppList','JobApplicationController@jobAppList');

            //职位排序
            Route::post('jobSort','JobController@sort');
            //添加企业账号
//            Route::post('addCompany','CompanyController@addCompany');
            //人才管理
            Route::post('talentList','InterviewController@talentList');



        });

        //面试管理
        Route::group(['prefix' => 'interview'],function (){
            //列表
            Route::post('list','InterviewController@list');
            //参加 不参加  变更时间
            Route::post('joinInterview','InterviewController@joinInterview');
            //进度记录
            Route::post('interviewSpeed','InterviewController@interviewSpeed');
            //面试日程
            Route::post('daylist','InterviewController@daylist');
            //面试记录
            Route::post('logList','InterviewController@logList');

            //待签约列表
            Route::get('signList', 'InterviewController@signList');
        });
        //签约管理
        Route::group(['prefix' => 'sing_contracts'],function (){
            Route::get('list','SignContractController@list');
            Route::post('sign','SignContractController@sign');
            Route::get('getSignDraft','SignContractController@getSignDraft');
            Route::post('signCancel','SignContractController@signCancel');
            Route::post('signUrge','SignContractController@signUrge');
            //详情
            Route::get("signDesc",'SignContractController@signDesc');
            //预览
            Route::get("signView",'SignContractController@signView');
        });


        #用户管理
        Route::group(['prefix' => 'user'],function(){
            //列表
            Route::post('list','UserController@userList');
            //更改用户状态
            Route::post('userStatus','UserController@userStatus');
            //重置用户密码
            Route::post('userUpdatePassword','UserController@userUpdatePassword');
            //添加用户
            Route::post('addUser','UserController@addUser');
            //编辑用户
            Route::post('editUser','UserController@editUser');
            //用户详情
            Route::post('descUser','UserController@descUser');
        });

        #外交入驻审核
        Route::group(['prefix' => 'check'],function(){
            //列表
            Route::any('list','MemberController@checkList');
            //详情
            Route::post('desc','MemberController@checkView');
            //审核驳回
            Route::post('reject','MemberController@checkReject');
            //审核通过
            Route::post('checkOk','MemberController@checkOk');
            //操作日志
            Route::post('checkLog','MemberController@checkLog');
            //外教入驻编辑
            Route::post('checkEdit','MemberController@checkEdit');
        });

        #外教列表管理
        Route::group(['prefix' => 'teach'],function (){
            //列表
            Route::any('list','MembersInfoController@checkList');
            //编辑
//            Route::any('edit','MembersInfoController@edit');
            //删除
//            Route::any('del','MembersInfoController@del');
            //查看
            Route::any('view','MembersInfoController@view');
            //新增外教用户账号
//            Route::post('addMember','MembersInfoController@addMember');
            //推荐外教 发送微信公众号通知
            Route::post('recommednTeach','MembersInfoController@recommednTeach');

            Route::post('add','MembersInfoController@add');
            Route::post('edit1','MembersInfoController@edit1');
            Route::post('delete','MembersInfoController@delete');
            Route::post('updateNotes','MembersInfoController@updateNotes');
            Route::post('updateAdviser','MembersInfoController@updateAdviser');
            Route::post('updateSignFlg','MembersInfoController@updateSignFlg');
            Route::post('updateCategory','MembersInfoController@updateCategory');
            //投递简历
            Route::post('jobApplication','JobApplicationController@jobApplication');
            Route::post('comments','MembersInfoController@comments');
            #修改备注接口
            Route::post('updateMemo','MembersInfoController@updateMemo');
            #推荐职位发送邮件
            Route::post('tuijianJob','MembersInfoController@tuijianJob');
        });
        #所有未交公共接口
        Route::get('public/memberAllList','PublicController@memberAllList');
        Route::get('public/getOpenJobList','JobController@getOpenJobList');

        #未读
        Route::get('notice','NoticeController@notice');
        #历史
        Route::get('history','NoticeController@history');
        #置为已读
        Route::post('read','NoticeController@read');

        #广告管理
        Route::group(['prefix' => 'advert'],function (){
            Route::get('list','AdvertController@list');
            Route::post('add','AdvertController@add');
            Route::post('updateStatus','AdvertController@updateStatus');
            Route::post('delete','AdvertController@delete');
            Route::post('edit','AdvertController@edit');
            Route::get('show','AdvertController@show');
        });

        #会员管理
        Route::resource('vip_action', 'VipActionController')->only([
            'index', 'store', 'show' , 'update'
        ]);

        #会员类型管理
        Route::resource('vip', 'VipController')->only([
            'index', 'store', 'show' , 'update'
        ]);



    });

});