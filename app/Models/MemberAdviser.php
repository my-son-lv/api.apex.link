<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MemberAdviser extends Model
{
    protected $table = 'member_advisers';

    /**
     * 该模型是否被自动维护时间戳
     *
     * @var bool
     */
    public $timestamps = true;

    protected $fillable = [
        "mid",
        "uid",
    ];
}
