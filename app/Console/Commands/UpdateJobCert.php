<?php

namespace App\Console\Commands;

use App\Models\Job;
use Illuminate\Console\Command;

class UpdateJobCert extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:UpdateJobCert';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '修改职位为 1需要 2不需要';

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
        $list = Job::all();
        foreach ($list as $k => $v){
                $v->update(['cert' => $v->cert ? 1 : 2]);
        }
    }
}
