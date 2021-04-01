<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Evaluates extends Model
{
    //国籍表
    protected $table = 'evaluates';

    /**
     * 该模型是否被自动维护时间戳
     *
     * @var bool
     */
    public $timestamps = true;
}
