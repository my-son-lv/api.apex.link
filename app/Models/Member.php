<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Member extends Model
{
    protected $table = 'members';

    /**
     * 该模型是否被自动维护时间戳
     *
     * @var bool
     */
    public $timestamps = true;

    public static function isExist($email){
        $count = Member::where('email',$email)->count();
        return $count ? true : false;
    }
    
    protected $fillable = [
        "email",
        "password",
        "nick_name",
        "user_id",
        "sign_id",
        "last_login_ip",
        "last_login_time",
        "register_ip",
        "register_time",
        "token",
        "token_expire_time",
        "invite_code",
    ];

    protected $hidden = ['password'];

    public function member_info(){
        return $this->hasOne(MemberInfo::class,'mid','id');
    }

    public function member_info_checked(){
        return $this->hasOne(MemberInfoChecked::class,'mid','id');
    }
}
