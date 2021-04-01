<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MemberInfo extends Model
{
    //
    protected $table = 'members_info';

    /**
     * 该模型是否被自动维护时间戳
     *
     * @var bool
     */
    public $timestamps = true;

    protected $fillable = [
            "mid",
            "name",
            "sex",
            "brithday",
            "nationality",
            "abroad_address",
            "china_address",
            "school",
            "university",
            "phone",
            "wechat",
            "celta_flg",
            "celta_img",
            "cert_other_flg",
            "major",
            "working_seniority",
            "working_city",
            "desc",
            "videos",
            "photos",
            "edu_cert_flg",
            "edu_cert_imgs",
            "edu_auth_flg",
            "edu_auth_imgs",
            "work_visa_flg",
            "science_flg",
            "commit_flg",
            "created_at",
            "updated_at",
            "work_flg",
            "work_start_time",
            "work_end_time",
            "last_name",
            "notes",
            "pay_type",
            "hot",
            "sign_status",
            "in_domestic",
            "visa_type",
            "country",
            "visa_exp_date",
            "school_type",
            "work_type",
            "student_age",
            "job_type",
            "job_work_type",
            "area_code",
            "category",
            "comm_type",
            "cert_other",
            "cert_other_img",
            "relocate",
            "relocate",
            "all_city",
            "university_img",
            "comments",
            "memo",
    ];
    /**
     * 关联外教教育经历表
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function education(){
        return $this->hasMany(Education::class,'mid','mid');
    }

    public function member(){
        return $this->hasMany(Member::class,'id','mid');
    }

    public function work(){
        return $this->hasMany(Workexperience::class,'mid','mid');
    }

    public function nationality_val(){
        return $this->hasOne(Country::class,'id','nationality');
    }

    public function videos_path(){
        return $this->hasOne(Files::class,'id','videos');
    }

    public function photos_path(){
        return $this->hasOne(Files::class,'id','photos');
    }

    public function edu_cert_imgs_path(){
        return $this->hasOne(Files::class,'id','edu_cert_imgs');
    }

    public function edu_auth_imgs_path(){
        return $this->hasOne(Files::class,'id','edu_auth_imgs');
    }

    public function celta_img_path(){
        return $this->hasOne(Files::class,'id','celta_img');
    }

    public function cert_other_img_path(){
        return $this->hasOne(Files::class,'id','cert_other_img');
    }

    public function country_val(){
        return $this->hasOne(Country::class,'id','country');
    }

    public function university_img_path(){
        return $this->hasOne(Files::class,'id','university_img');
    }
}
