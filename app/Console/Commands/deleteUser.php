<?php

namespace App\Console\Commands;

use App\Models\Collect;
use App\Models\CompanyAdvier;
use App\Models\CompanyCheckLog;
use App\Models\Companys;
use App\Models\Evaluates;
use App\Models\ImUser;
use App\Models\Interview;
use App\Models\InterviewLogs;
use App\Models\Job;
use Illuminate\Console\Command;

class deleteUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     * @int type 用户类型 1企业用户 2外交用户
     * @int id 用户id
     */
    protected $signature = 'command:deleteUser {type} {id} ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $id     = $this->argument('id'); // 不指定参数名的情况下用argument
        $type   = $this->argument('type'); // 不指定参数名的情况下用argument
        switch ($type){
            case 1://删除企业

                //删除企业表
                Companys::where('id',$id)->delete();
                //删除顾问表
                CompanyAdvier::where('cid',$id)->delete();
                //删除审核记录
                CompanyCheckLog::where('cid',$id)->delete();
                //删除职位表
                Job::where('cid',$id)->delete();
                //删除面试表
                $in_id =  Interview::where('cid',$id)->get(['id']);
                Interview::where('cid',$id)->delete();
                //删除面试记录表
                InterviewLogs::whereIn('vid',$in_id)->delete();
                //删除面试评价表
                Evaluates::where('cid',$id)->delete();
                //删除IM表
                ImUser::where('id',$id)->where('type',2)->delete();
                //删除收藏表
                Collect::where('cid',$id)->delete();
                break;
            case 2://删除外教
                //用户表

                //删除草稿表

                //删除正式表

                //删除审核记录表

                //删除收藏表

                //删除面试表

                //删除面试记录操作表

                //删除面试评价表

                //删除IM表



                break;
        }

        //
    }
}
