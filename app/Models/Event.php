<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    protected $table = 'events';

    /**
     * 该模型是否被自动维护时间戳
     *
     * @var bool
     */
    public $timestamps = true;

    protected $fillable = ['info','type','user_id'];
    /**
     * 添加事件
     * @param $info
     * @param $type
     * @return mixed
     */
    /*public static function addEvent($info,$type,$operation_id,$userId,$name = ''){
        $info = $name ? $info.' '.self::returnTypeMsg($type) : $info.' '.self::returnTypeMsg($type).' '.$name;
        return self::create(['info' => $info,'type' => $type, 'operation_id' => $operation_id,'user_id' => $userId]);
    }*/

    /**
     * 添加事件
     * @param $info  描述
     * @param $user  用户
     * @param int $type 类型 1外教 2企业
     * @return mixed
     */
    public static function addEvent($info,$user,$type = 1){
//        $info = $name ? $info.' '.self::returnTypeMsg($type) : $info.' '.self::returnTypeMsg($type).' '.$name;
        return self::create(['info' => $info,'type' => $type, 'user_id' => $user]);
    }

    private static function returnTypeMsg($type){
        $msg = '';
        switch ($type){
            case 1:
                $msg = '注册了平台';
                break;
            case 2:
                $msg = '提交入驻申请';
                break;
            case 3:
                $msg = '信息自动审核通过';
                break;
            case 4:
                $msg = '驳回入驻申请';
                break;
            case 5:
                $msg = '通过入驻申请';
                break;
            case 6:
                $msg = '修改了用户信息';//外教
                break;
            case 7:
                $msg = '修改了招聘需求';
                break;
            case 8:
                $msg = '将顾问变更为';
                break;
            case 9:
                $msg = '添加了用户';
                break;
        }
        return $msg;
    }
}
