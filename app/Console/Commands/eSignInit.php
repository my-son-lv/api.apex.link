<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class eSignInit extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:eSignInit';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'e签宝项目初始化 每次初始化请重启JAVA SDK环境！';

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
        //初始化e签宝
        $config = [
            'projectConfig' => [
                'projectId'     => config('esign.E_SIGN_APP_ID'),
                'projectSecret' => config('esign.E_SIGN_SECRET'),
                'itsmApiUrl'    => config('esign.E_SIGN_ITSM_API_URL'),
            ]
        ];
        $header = array(
            "Content-Type: application/json",
            "Accept: application/json"
            );
        $res = postJsonCurl(config('esign.E_SIGN_HOST_URL').config('esign.E_SIGN_INIT_URL'),$config,$header);
        var_dump($res);exit;
    }
}
