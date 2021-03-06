<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default filesystem disk that should be used
    | by the framework. The "local" disk, as well as a variety of cloud
    | based disks are available to your application. Just store away!
    |
    */

    'default' => env('FILESYSTEM_DRIVER', 'local'),

    /*
    |--------------------------------------------------------------------------
    | Default Cloud Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Many applications store files both locally and in the cloud. For this
    | reason, you may specify a default "cloud" driver here. This driver
    | will be bound as the Cloud disk implementation in the container.
    |
    */

    'cloud' => env('FILESYSTEM_CLOUD', 's3'),

    /*
    |--------------------------------------------------------------------------
    | Filesystem Disks
    |--------------------------------------------------------------------------
    |
    | Here you may configure as many filesystem "disks" as you wish, and you
    | may even configure multiple disks of the same driver. Defaults have
    | been setup for each driver as an example of the required options.
    |
    | Supported Drivers: "local", "ftp", "s3", "rackspace"
    |
    */
    /**
     * 上传图片最大限制 默认5M
     */
    'UPLOAD_IMAGE_MAX_SIZE' => env('UPLOAD_IMAGE_MAX_SIZE', 5),

    /**
     * 上传图片扩展 默认['jpg','png','jepg','gif']
     */
    'UPLOAD_IMAGE_EXT' => env('UPLOAD_IMAGE_EXT', 'jpg,png,jepg,gif'),

    /**
     * 上传视频最大限制 默认50M
     */
    'UPLOAD_VIDEOS_MAX_SIZE' => env('UPLOAD_VIDEOS_MAX_SIZE', 50),

    /**
     * 上传视频扩展 默认['jpg','png','jepg','gif']
     */
    'UPLOAD_VIDEOS_EXT' => env('UPLOAD_VIDEOS_EXT', 'avi,mp4,3gp,mov,rmvb,rm,flv'),

    /**
     * 上传简历
     */
    'UPLOAD_DOC_MAX_SIZE' => env('UPLOAD_DOC_MAX_SIZE', 30),

    /**
     * 上传图片扩展 默认['jpg','png','jepg','gif']
     */
    'UPLOAD_DOC_EXT' => env('UPLOAD_DOC_EXT', 'doc,docx,pdf'),

    'disks' => [

        'local' => [
            'driver' => 'local',
            'root' => storage_path('app'),
        ],

        'public' => [
            'driver' => 'local',
            'root' => storage_path('app/public'),
            'url' => env('APP_URL').'/storage',
            'visibility' => 'public',
        ],

        's3' => [
            'driver' => 's3',
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION'),
            'bucket' => env('AWS_BUCKET'),
        ],

        'oss' => [
            'driver'     => 'oss',
            'access_id'  => env('OSS_ACCESS_ID',''),//Your Aliyun OSS AccessKeyId
            'access_key' => env('OSS_ACCESS_KEY',''),//Your Aliyun OSS AccessKeySecret
            'bucket'     => env('OSS_BUCKET',''),//OSS bucket name
            'endpoint'   => env('OSS_ENDPOINT','oss-cn-beijing.aliyuncs.com'), //<the endpoint of OSS, E.g: oss-cn-hangzhou.aliyuncs.com | custom domain, E.g:img.abc.com> OSS 外网节点或自定义外部域名
            //'endpoint_internal' => '', //<internal endpoint [OSS内网节点] 如：oss-cn-shenzhen-internal.aliyuncs.com> v2.0.4 新增配置属性，如果为空，则默认使用 endpoint 配置(由于内网上传有点小问题未解决，请大家暂时不要使用内网节点上传，正在与阿里技术沟通中)
            'cdnDomain'  => env('OSS_CDN_DOMAIN',''), //<CDN domain, cdn域名> 如果isCName为true, getUrl会判断cdnDomain是否设定来决定返回的url，如果cdnDomain未设置，则使用endpoint来生成url，否则使用cdn
            'ssl'        => true, // true to use 'https://' and false to use 'http://'. default is false,
            'isCName'    => true, // 是否使用自定义域名,true: 则Storage.url()会使用自定义的cdn或域名生成文件url， false: 则使用外部节点生成url
            'debug'      => false,
        ],

    ],

];
