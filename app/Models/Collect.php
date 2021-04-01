<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Collect extends Model
{
    //
    protected $table = 'collect';

    /**
     * 该模型是否被自动维护时间戳
     *
     * @var bool
     */
    public $timestamps = true;

    public function member_info(){
        return $this->hasOne(MemberInfo::class,'mid','mid');
    }

    public function company(){
        return $this->hasOne(Companys::class,'id','cid');
    }

    public function member(){
        return $this->hasOne(Member::class,'id','mid');
    }
}
