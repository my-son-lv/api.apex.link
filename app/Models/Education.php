<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Education extends Model
{
    //
    protected $table = 'education';

    protected $fillable = ['mid','edu_start_time','edu_end_time','school','major','show'];

    /**
     * 关联外教审核表
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function memberCheck(){
        return $this->belongsTo(MemberInfoChecked::class,'id','mid');
    }
    /**
     * 关联外教正式表
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function memberInfo(){
        return $this->belongsTo(MemberInfo::class,'id','mid');
    }
}
