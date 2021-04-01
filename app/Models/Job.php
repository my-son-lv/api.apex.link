<?php

namespace App\Models;

use Faker\Provider\Company;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Job extends Model
{
    //
    protected $table = 'job';

    /**
     * 该模型是否被自动维护时间戳
     *
     * @var bool
     */
    public $timestamps = true;

    protected $hidden = ['deleted_at'];

    protected $fillable = [
        'cid',
        'name',
        'type',
        'first_language',
        'job_city',
        'language',
        'job_type',
        'job_week_day',
        'job_day_time',
        'pay_type',
        'pay',
        'pay_unit',
        'money_type',
        'edu_type',
        'cert',
        'job_year',
        'num',
        'start_time',
        'end_time',
        'benefits',
        'job_info',
        'memo',
        'status',
        'colour',
        'sex',
        'work_type',
        'visa_ask',
        'student_age',
        'top',
        'top_exp_time',
        'flg',
        'benefits_tag',
        'teaching_time',
    ];

    //关联企业表
    public function company()
    {
        return $this->hasOne(Companys::class, 'id', 'cid');
    }
}
