<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SignContractLog extends Model
{
    //
    protected $table = 'sign_contract_logs';

    /**
     * 该模型是否被自动维护时间戳
     *
     * @var bool
     */
    public $timestamps = true;

    public static function addLog($user_id,$type,$sign_id,$info){
        $model = new SignContractLog();
        $model->user_id = $user_id;
        $model->type    = $type;
        $model->sign_id = $sign_id;
        $info && $model->info    = $info;
        if($model->save()){
            return $model;
        }else{
            return false;
        }
    }
}
