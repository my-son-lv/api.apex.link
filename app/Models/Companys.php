<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Companys extends Model
{
    //
    protected $table = 'companys';

    /**
     * 该模型是否被自动维护时间戳
     *
     * @var bool
     */
    public $timestamps = true;

    protected $hidden = ['password'];

    protected $fillable = [
        'phone',
        'password',
        'company_name',
        'business_img',
        'type',
        'city',
        'address',
        'talent',
        'talent_img',
        'student_age',
        'abroad_staff',
        'needs_num',
        'pay',
        'contact',
        'contact_phone',
        'work_email',
        'last_login_ip',
        'last_login_time',
        'register_ip',
        'register_time',
        'token',
        'token_expire_time',
        'created_at',
        'updated_at',
        'status',
        'check_log_id',
        'submit_num',
        'gw_flg',
        'logo',
        'business_name',
        'business_flg',
        'school_img_1',
        'school_img_2',
        'submit_type',
        'invite_code',
        'company_name_bak',
        'check_ok_time',
        'unionid',
        'memo',
        'school_en_info',
    ];
}
