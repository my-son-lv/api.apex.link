<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    protected $table = 'user';

    /**
     * 该模型是否被自动维护时间戳
     *
     * @var bool
     */
    public $timestamps = true;

    public static function isEmailExist($email,$id=false){
        $count = User::where('email',$email);
        if($id){
            $count = $count->where('id','<>',$id);
        }
        $count = $count->count();
        return $count ? true : false;
    }

    public static function isPhoneExist($phone,$id = false){
        $count = User::where('email',$phone);
        if($id){
            $count = $count->where('id','<>',$id);
        }
        $count = $count->count();
        return $count ? true : false;
    }
}
