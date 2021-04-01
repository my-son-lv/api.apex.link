<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Code extends Model
{
    protected $table = 'code';

    /**
     * 该模型是否被自动维护时间戳
     *
     * @var bool
     */
    public $timestamps = true;

    /**
     * 添加验证码记录
     * @param $email
     * @param $code
     * @return bool
     */
    public static function addCode($email,$code){
        Code::where('email',$email)->delete();
        $model = new Code();
        $model->email = $email;
        $model->code  = $code;
        if($model->save()){
            return true;
        }else{
            return false;
        }
    }

    public static function delCode($email){
        Code::where('email',$email)->delete();
        return true;
    }
}
