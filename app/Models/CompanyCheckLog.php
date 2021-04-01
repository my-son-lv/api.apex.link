<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyCheckLog extends Model
{
    //
    protected $table = 'company_check_log';

    /**
     * 该模型是否被自动维护时间戳
     *
     * @var bool
     */
    public $timestamps = true;
}
