<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyViewLog extends Model
{
    protected $table = 'company_view_logs';

    /**
     * 该模型是否被自动维护时间戳
     *
     * @var bool
     */
    public $timestamps = true;
}
