<?php

namespace App\Console\Commands;

use App\Models\Job;
use Illuminate\Console\Command;

class UpdateJobPay extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:UpdateJobPay';

    /**
     * The console command description.
     * pay 薪资*1000
     * pay_unit 1小时 2每天 3每周 4每月 5每年
     *
     * @var string
     */
    protected $description = '修改职位薪资单位（元） 修改薪资单位为月';

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
        echo "开始处理数据" . PHP_EOL;
        $job = Job::all();
        foreach ($job as $k => $v) {
            //老数据修改薪资 * 1000（元）
            $job_pay = explode(',',$v->pay);
            $job_pay_new = $job_pay[0] * 1000 . ',' . $job_pay[1] * 1000;
            //老数据修改单位：月薪
            $job_unit = 4;
            $model = Job::where('id', $v->id)->update(['pay' => $job_pay_new , 'pay_unit' => $job_unit]);
            echo '已完成 id：'.$v->id.PHP_EOL;
        }

    }
}
