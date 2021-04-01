<?php

namespace App\Console\Commands;

use App\Models\Member;
use App\Models\MemberInfo;
use App\Models\MemberInfoChecked;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class UpdateMembeData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:UpdateMemberData';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '';

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
        $memberInfoChecked = new MemberInfoChecked();
        DB::beginTransaction();
        try{
            $list = Member::all();
            foreach ($list as $k => $v){
                $flg = MemberInfoChecked::where('mid',$v->id)->count();
                if(!$flg){
                    $model = new MemberInfoChecked();
                    $model->mid = $v->id;
                    if(!$model->save()){
                        echo $v->id."插入失败了".PHP_EOL;
                    }else{
                        echo $v->id."成功".PHP_EOL;
                    }
                }
            }

            /*$flg1 = $memberInfoChecked->where('id','>',0)->update(['china_address' => 110100]);
            echo "更新成功-城市：".$flg1.PHP_EOL;
            $flg2 = $memberInfoChecked->increment('university');
            echo "更新成功-学历：".$flg2.PHP_EOL;
            $flg3 = $memberInfoChecked->increment('working_seniority');
            echo "更新成功-工作年限：".$flg3.PHP_EOL;
            $memberInfo = new MemberInfo();
            $flg4 = $memberInfo->where('id','>',0)->update(['china_address' => 110100]);
            echo "更新成功-城市：".$flg4.PHP_EOL;
            $flg5 =$memberInfo->increment('university');
            echo "更新成功-学历：".$flg5.PHP_EOL;
            $flg6 =$memberInfo->increment('working_seniority');
            echo "更新成功-工作年限：".$flg6.PHP_EOL;
            DB::commit();
            echo "更新成功";*/
        }catch (\Exception $e){
            DB::rollback();
            echo $e->getMessage();
        }

    }
}
