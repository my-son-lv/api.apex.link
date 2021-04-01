<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InterviewLogs extends Model
{
    //
    protected $table = 'interview_logs';

    /**
     * 该模型是否被自动维护时间戳
     *
     * @var bool
     */
    public $timestamps = true;

    protected $fillable = ['id','vid','info','info1'];

    public function interview(){
        return $this->hasOne(Interview::class,'id','vid');
    }
}
