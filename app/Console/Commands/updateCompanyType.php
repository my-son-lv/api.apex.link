<?php

namespace App\Console\Commands;

use App\Models\Companys;
use Illuminate\Console\Command;

class updateCompanyType extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:updateCompanyType';
    //培训学校1, 幼儿园2, 小学3, 初中4, 高中5, 大学6
    //{
    //          value: "培训机构",1
    //          id: 1,
    //        },
    //        {
    //          value: "公立学校", 3,4,5,6
    //          id: 2,
    //        },
    //        {
    //          value: "私立学校", 3,4,5,6
    //          id: 3,
    //        },
    //        {
    //          value: "中介机构",1
    //          id: 4,
    //        },
    //        {
    //          value: "幼儿园",2
    //          id: 5,
    //        },
    //        {
    //          value: "其他", 6
    //          id: 6,
    //        },
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '修改学校类型 同时为多选';

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
        $list = Companys::all();
        foreach ($list as $k => $v){
            if($v->type){
                switch ($v->type ){
                    case 2:
                    case 3:
                        $v->type = '3,4,5,6';
                        break;
                    case 4:
                        $v->type = '1';
                        break;
                    case 5:
                        $v->type = '2';
                        break;
                }
                $v->save();
            }
        }
    }
}
