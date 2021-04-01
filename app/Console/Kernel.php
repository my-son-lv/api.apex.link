<?php

namespace App\Console;

use App\Models\Companys;
use App\Models\Files;
use App\Models\ImUser;
use App\Models\Interview;
use App\Models\InterviewLogs;
use App\Models\Member;
use App\Models\MemberInfo;
use App\Models\MemberInfoChecked;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //创建service层
        Commands\AddService::class,

        //
        //创建聊天用户
        Commands\CreateImUser::class,
        //修改老数据
        Commands\UpdateMembeData::class,
        //删除用户 1企业 2外交
        Commands\deleteUser::class,
        //微信抓文章
        Commands\WxEnArt::class,
        //定时审核外交
        Commands\checkMember::class,
        //提前一个小时发送面试剩余一小时通知
        Commands\TeachInterNotice::class,
        //给企业添加邀请码
        Commands\AddCompanyCode::class,
        //每周给用户发送消息
        Commands\SendTextToUser::class,
        //修改聊天用户默认头衔
        Commands\UpdateTeachDefaultPhoto::class,
        //企业会员自动过期
        Commands\VipCheck::class,
        //职位置顶过期自动处理
        Commands\JobExp::class,
        //处理旧数据 教育经历放在新的表中
        Commands\UpdateTeachData_EDU::class,
        //职位积分计算旧数据
        Commands\JobLevelCount::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('SendTextToUser')->weeklyOn(4, $time = '12:00');
        $schedule->command('command:WxEnArt')->dailyAt('6:00');
        $schedule->command('checkMember')->everyMinute();
        $schedule->command('VipCheck')->everyMinute();
        $schedule->command('JobExp')->everyMinute();
        $schedule->command('TeachInterNotice')->everyMinute();
        $schedule->call(function () {
            ##修改面试为面试中
            /*$list = Interview::whereRaw(DB::raw('left(inte_time,16)="'.date('Y-m-d H:i').'"'))->where('status',1)->get();
            foreach ($list as $k => $v){
                if($v->status == 1){
                    $flg = Interview::where('id',$v->id)->update(['status' => 2]);
                }
            }*/


            ###待面试修改为已过期 超过2小时
            $list = Interview::where('status',1)->get();
            foreach ($list as $k => $v){
                //如果超出2小时还没有进入面试中 则修改为已过期
                if((time() - strtotime($v->inte_time)) > 60 * 60  * 2   ){

                    $modelLog = new InterviewLogs();
                    $modelLog->vid = $v->id;
                    $company = Companys::find($v->cid);
                    $teach = MemberInfo::where('mid',$v->mid)->first();
                    $msgData['company_name'] = $company->company_name;
                    $msgData['teach_name'] = $teach->name . ' ' .$teach->last_name;
                    $msgData['time'] = $v->inte_time;
                    $msg = interViewLogMsg($msgData,10);
                    $modelLog->info    = $msg[0];
                    $modelLog->info1    = $msg[1];
                    $modelLog->save();

                    $flg = Interview::where('id',$v->id)->update(['status' => 8]);



                }
            }
        })->everyMinute();

    }


    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
