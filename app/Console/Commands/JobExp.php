<?php

namespace App\Console\Commands;

use App\Models\Job;
use Illuminate\Console\Command;

class JobExp extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'JobExp';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '职位过期自动处理';

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
        $list = Job::where('top',1)->get();
        foreach ($list as $k => $v){
            if(strtotime($v->top_exp_time) < time() ){
                Job::where('id',$v->id)->update(['top' => 0, 'top_exp_time' => null]);
            }
        }
    }
}
