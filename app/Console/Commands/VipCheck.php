<?php

namespace App\Console\Commands;

use App\Models\Companys;
use App\Models\Job;
use App\Models\VipAction;
use Illuminate\Console\Command;

class VipCheck extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'VipCheck';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '会员自动过期';

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
        //
        //查询所有会员
        $list = VipAction::where('status',2)->get();
        foreach ($list as $k => $v){
            if(strtotime($v->end_time) < time()){
                //重置状态
                VipAction::where('id',$v->id)->update(['status' => 4]);
                //修改企业状态
                Companys::where('id',$v->cid)->update(['vip_actions_id' => null]);
                //会员到期取消所有职位置顶```
                Job::where('cid',$v->cid)->update(['top' => 0,'top_exp_time' => null ,'status' => 2]);
            }
        }
    }
}
