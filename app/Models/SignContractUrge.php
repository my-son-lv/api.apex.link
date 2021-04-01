<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SignContractUrge extends Model
{
    protected $table = 'sign_contract_urges';

    /**
     * 该模型是否被自动维护时间戳
     *
     * @var bool
     */
    public $timestamps = true;

    protected $fillable = ['sign_id','user_id','notice'];
}
