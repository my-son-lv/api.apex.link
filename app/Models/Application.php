<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Application extends Model
{
    //
    protected $table = 'applications';

    /**
     * 该模型是否被自动维护时间戳
     *
     * @var bool
     */
    public $timestamps = true;

    protected $fillable = [
        'cid',
        'vip_id',
        'company_name',
        'company_tel',
        'ip'
    ];
}
