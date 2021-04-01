<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MemberInfoCheckedLog extends Model
{
    protected $table = 'members_info_check_log';

    /**
     * 该模型是否被自动维护时间戳
     *
     * @var bool
     */
    public $timestamps = true;

    public static function addLog($mid,$status , $info , $admin_id ){
        $resModel = new MemberInfoCheckedLog();
        $resModel->mid      = $mid;
        $resModel->status   = $status;
        $resModel->info     = $info;
        $resModel->uid = $admin_id;
        if($resModel->save()){
            return $resModel->getQueueableId();
        }else{
            return false;
        }
    }
}
