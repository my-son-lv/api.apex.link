<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Interview extends Model
{

    protected $table = 'interview';
    protected  $hidden =['deleted_at'];

    /**
     * 该模型是否被自动维护时间戳
     *
     * @var bool
     */
    public $timestamps = true;
    protected $fillable = ['id','mid','cid','jid','inte_time','status','eval_flg','up_flg','info'];
}
