<?php

return [
        ##实名认证

        //实名认证url
        'E_SIGN_AUTO_URL'       => env('E_SIGN_AUTO_URL',''),
        //登录获取token
        'E_SIGN_AUTO_LOGIN_URL'       => '/v1/oauth2/access_token',
        //创建个人账号
        'E_SIGN_AUTO_CREATE_USER_URL'  =>'/v1/accounts/createByThirdPartyUserId',
        //创建企业账号
        'E_SGIN_AUTO_CREATE_ORGANIZE_URL' => '/v1/organizations/createByThirdPartyUserId',
        //获取上传地址
        'E_SGIN_GET_UPLOAD_URL' => '/v1/files/getUploadUrl',
        //获取上传地址
        'E_SGIN_CREATE_FLOW_ONE_STEP' => '/api/v2/signflows/createFlowOneStep',











        //获取实名认证页面
        ///v2/identity/auth/web/d9cc39be64b44535a1945d24fbed4905/orgIdentityUrl

        ##签章
        //APPID
        'E_SIGN_APP_ID'             =>  env('E_SIGN_APP_ID',''),
        //SECRET
        'E_SIGN_SECRET'             =>  env('E_SIGN_SECRET',''),
        //ITSM_API_URL场景url  测试URL/正式URL
        'E_SIGN_ITSM_API_URL'       =>  env('E_SIGN_ITSM_API_URL',''),


        //e签宝本地url
        'E_SIGN_HOST_URL'   =>  env('E_SIGN_URL_HOST','http://localhost').':'.env('E_SIGN_URL_PORT','8080'),
        //初始化URL
        'E_SIGN_INIT_URL'   =>  '/tech-sdkwrapper/timevale/init',
        //创建个人账户
        'E_SIGN_ADD_PERSON_URL'   =>  '/tech-sdkwrapper/timevale/account/addPerson',
        //更新个人账户
        'E_SIGN_UPDATE_PERSON_URL'   =>  '/tech-sdkwrapper/timevale/account/addPerson',
        //创建企业账户
        'E_SIGN_ADD_ORGANIZE_URL'   =>  '/tech-sdkwrapper/timevale/account/addOrganize',
        //更新企业账户
        'E_SIGN_UPDATE_ORGANIZE_URL'   =>  '/tech-sdkwrapper/timevale/account/updateOrganize',
        //平台自身摘要签署
        'E_SIGN_SELF_FILE_SIGN'        =>'/tech-sdkwrapper/timevale/sign/selfFileSign',
        //平台用户PDF摘要签署
        'E_SIGN_USER_FILE_SIGN'      =>      '/tech-sdkwrapper/timevale/sign/userFileSign',
        //创建企业用户印章
        'E_SIGN_ADD_ORGANIZE_SEAL'    => '/tech-sdkwrapper/timevale/seal/addOrganizeSeal',

    ];