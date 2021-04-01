<?php

namespace App\Console\Commands;

use App\Models\Companys;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class AddCompanyCode extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'AddCompanyCode';

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
        //
        DB::beginTransaction();
        $list = Companys::all();
        foreach ($list as $k => $v){
            $v->code = makeCouponCard();
            if(!$v->save()){
                DB::rollBack();
                exit('出错了');
            }
        }
        DB::commit();
        echo "完成了";
    }
}
