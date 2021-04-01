<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SignContract extends Model
{
    //
    protected $table = 'sign_contracts';

    /**
     * 该模型是否被自动维护时间戳
     *
     * @var bool
     */
    public $timestamps = true;

    protected $fillable=[
            'interview_id',
            'cid',
            'user_id',
            'contract_id',
            'name',
            'a_name',
            'a_phone',
            'b_name',
            'b_phone',
            'b_company_name',
            'end_time',
            'memo',
            'contract_data',
            'status',
            'start_date',
            'notice',
            'auth_type',
    ];

    public function user(){
        return $this->hasOne(User::class, 'id', 'user_id');
    }
}
