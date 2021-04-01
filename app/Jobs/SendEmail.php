<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Mail;

class SendEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;



    protected  $data;

    /**
     * 任务最大尝试次数。
     *
     * @var int
     */
    public $tries = 1;
    /**
     * 任务运行的超时时间。
     *
     * @var int
     */
    public $timeout = 10;



    public function __construct($data)
    {
        //
        $this->data = $data;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $data = $this->data;
//        echo "开始发送->".$data['email'].">>>>>>>>>>>>>>>>>";
//        $t1=microtime(true);
        Mail::send('email.'.$data['template'],['data' => $data],function($message)use($data){
            $message->subject($data['title']);
            $message->to($data['email']);
        });
//        $t2=microtime(true);
//        echo "发送成功->".$data['email'].'        用时：'.round($time1=$t2-$t1,5)."秒".PHP_EOL;
    }
}
