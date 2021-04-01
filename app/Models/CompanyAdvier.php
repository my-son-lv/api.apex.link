<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyAdvier extends Model
{
    protected $table = 'company_adviser';

    /**
     * 该模型是否被自动维护时间戳
     *
     * @var bool
     */
    public $timestamps = true;

    protected $fillable = ['cid','uid'];

}
