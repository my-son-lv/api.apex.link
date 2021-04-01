<?php

namespace App\Http\Controllers\Index;

use App\Models\Companys;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WxCompanyController extends Controller
{

    public function updateWx(Request $request){
        $wx_code    = $request->get('wx_code','');
        $token      = $request->get('token'.'');
        //解密open_id
        Log::info('小程序CODE:'.json_encode($wx_code));
        $miniProgram = \EasyWeChat::miniProgram();
        $wx_data = $miniProgram->auth->session($wx_code);
        Log::info('小程序CODE解密:'.json_encode($wx_data));
        if(@$wx_data['openid']){
            if( $request->company->open_id !=$wx_data['openid']){
                DB::beginTransaction();
                try {
                    $data['unionid'] =  isset($wx_data['unionid']) ?  $wx_data['unionid'] : $request->company->unionid;
                    Companys::where('open_id',$wx_data['openid'])->update(['open_id' => null, 'session_key' => null, 'unionid' => null ]);
                    $data = ['open_id' => $wx_data['openid'],'session_key' => $wx_data['session_key']];
                    Companys::where('token',$token)->update($data);
                    DB::commit();
                    return $this->success();
                }catch (\Exception $e){
                    DB::rollbakc();
                    return $this->fail(100000,$e->getMessage());
                }
            }else{
                return $this->fail(100012);
            }
        }else{
            return $this->fail(100000,$wx_data['errmsg']);
        }


    }
}
