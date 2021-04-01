<?php

namespace App\Console\Commands;

use App\Models\Education;
use App\Models\MemberInfo;
use Illuminate\Console\Command;

class UpdateTeachData_EDU extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'UpdateTeachData_EDU';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '修改外教专业学校到教育经历表中';

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
        $list = MemberInfo::all();
        foreach ($list as $k => $v){
            if($v->school || $v->major ){
                Education::create([
                    'mid' => $v->mid ,
                    'school'    => $v->school,
                    'major'     => $v->major,
                ]);
            }
        }
    }
}
