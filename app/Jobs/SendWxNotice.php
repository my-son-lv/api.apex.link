<?php

namespace App\Jobs;

use App\Services\WxTempNoticeService;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class SendWxNotice implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    private  $data ;

    /**
     * 任务最大尝试次数。
     *
     * @var int
     */
    public $tries = 3;
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
        $app = \EasyWeChat::officialAccount();
        $wxTempNoticeService = new WxTempNoticeService();
        $res = $app->template_message->send($wxTempNoticeService->returnTempData($this->data));
    }
}
