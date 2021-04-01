<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VipAction extends Model
{
    protected $table = 'vip_actions';

    protected $fillable = ['cid','vip_id','pay','operator','operator_tel','type','payee','pay_date','memo','status','start_time','end_time','yy_job_num','yy_top','yy_down','user_id'];

    /**
     * 关联企业
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function company(){
        return $this->hasOne(Companys::class , 'id' , 'cid');
    }

    /**
     * 关联会员类型
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function vip(){
        return $this->hasOne(Vip::class,'id','vip_id');
    }

    public function user (){
        return $this->hasOne(User::class,'id','user_id');
    }
}
