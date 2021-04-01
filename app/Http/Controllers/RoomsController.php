<?php

namespace App\Http\Controllers;

use App\Models\Companys;
use App\Models\Interview;
use App\Models\Member;
use App\Models\MemberInfo;
use App\Models\Rooms;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Tencent;
use TencentCloud\Common\Credential;
use TencentCloud\Common\Profile\ClientProfile;
use TencentCloud\Common\Profile\HttpProfile;
use TencentCloud\Common\Exception\TencentCloudSDKException;
use TencentCloud\Trtc\V20190722\TrtcClient;
use TencentCloud\Trtc\V20190722\Models\DismissRoomRequest;

class RoomsController extends Controller
{


    //
    public function intoRoom(Request $request){
        $type       = $request->get('type',1);//1顾问 2企业 3外教
        $inter_id   = $request->get('inter_id',0);//面试记录id
        $account    = $request->get('account','');
        $token      = $request->get('token','');
        if(!$type || !$inter_id || !$account || !$token){
            return $this->fail(100001);
        }
        $interView = Interview::find($inter_id);
        if(!$interView){
            return $this->fail(2000201);
        }
        if($interView->status >2 || $interView->status < 1){
            return $this->fail(2000201);
        }
        if($interView->status  == 1){
            if((strtotime($interView->inte_time) - time()) > 30 * 60  ){
                return $this->fail(2000206);
            }
        }
        DB::beginTransaction();
        $model = Rooms::where('inter_id',$inter_id)->first();
        if(!$model) $model = new Rooms();
        $userData = [];
        switch ($type){
            case 1:
                $interView->status = 2;
                $model->admin_flg = 2;
                $user = User::where('token',$token)->first();
                $userData = ['name' => $user->name];
                break;
            case 2:
                $model->company_flg = 2;
                $user = Companys::where('token',$token)->first();
                $userData = ['company_name' => $user->company_name,'contact' => $user->contact];
                break;
            case 3:
                $user = Member::where('token',$token)->first();
                $memeber = MemberInfo::where('mid',$user->id)->first();
                $userData = ['first_name' => $memeber->name,'last_name' => $memeber->last_name];
                $model->teach_flg = 2;
                break;
        }
        $model->inter_id = $inter_id;
        if($model->save() && $interView->save()){
            DB::commit();
            $api = new Tencent\TLSSigAPIv2(config('videos.SDK_APP_ID'),config('videos.USER_SIG'));
            $sig = $api->genSig($account);
            return $this->success([
                'room_id'       => $model->id,
                'USER_SIGN'     =>  $sig,
                'SDK_APP_ID'    => config('videos.SDK_APP_ID'),
                'user'          => $userData,
//                'USER_SIG'    => config('videos.USER_SIG')
            ]);
        }else{
            DB::rollback();
            return $this->fail();
        }
    }
}
