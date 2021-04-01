<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JobApplication extends Model
{
    //
    protected $table = 'job_applications';

    /**
     * 该模型是否被自动维护时间戳
     *
     * @var bool
     */
    public $timestamps = true;

    protected $fillable = [
        'mid',
        'cid',
        'jid',
        'read_flg',
        'result',
    ];

    public function member_info(){
        return $this->belongsTo(MemberInfo::class,'mid','mid');
    }
}
