<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ImUser extends Model
{
    //
    //国籍表
    protected $table = 'im_users';

    /**
     * 该模型是否被自动维护时间戳
     *
     * @var bool
     */
    public $timestamps = true;

    protected $fillable = ['user_id','type'];
}
